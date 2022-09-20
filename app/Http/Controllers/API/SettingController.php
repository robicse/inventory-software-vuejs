<?php

namespace App\Http\Controllers\API;

use App\Libraries\Email;
use App\Models\Setting;
use App\Models\ShortcutKey;
use Cache;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Libraries\Permissions;
use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Libraries\imageHandler;
use App\Http\Controllers\LocalizationController;
use Illuminate\Filesystem\Filesystem;
use PDF;


class SettingController extends Controller
{

    public function permissionCheck()
    {
        $controller = new Permissions;
        return $controller;
    }

    public function index()
    {
        $tabName = '';
        $routeName = '';
        if(isset($_GET['tab_name'])){
            $tabName = $_GET['tab_name'];
        }
        if(isset($_GET['route_name'])){
            $routeName = $_GET['route_name'];
        }
        return view('settings.SettingsIndex',['tab_name'=>$tabName, 'route_name'=>$routeName]);
    }

    public function getAppPublicPath()
    {
        $publicPath = $this->publicPath;

        return $publicPath;
    }

    public function getAppLogo()
    {
        return Config::get('app_logo');
    }

    public function cacheData()
    {
        return Setting::cacheDataSave();
    }

    public function emailSettingForm()
    {
        if ($this->permissionCheck()->isAdmin()) {
            return view('setting.email');
        } else {
            $response = [
                'msg' => Lang::get('lang.permission_error'),
                'template' => Lang::get('lang.permission_is_not_available')
            ];

            return response()->json($response, 401);
        }
    }

    public function emailSettingSave(Request $request)
    {
        $this->validate($request, [
            'email_from_name' => 'required',
            'email_from_address' => 'required',
            'email_driver' => 'required',
        ]);

        $name = $request->email_from_name;
        $address = $request->email_from_address;
        $driver = $request->email_driver;
        $host = $request->email_smtp_host;
        $port = $request->email_port;
        $pass = $request->email_smtp_password;
        $type = $request->email_encryption_type;
        $mailgunDomain = $request->mailgun_domain;
        $mailgunApi = $request->mailgun_api;
        $mandrill = $request->mandrill;
        $sparkpost = $request->sparkpost;

        $emailData = array(
            'email_from_name' => $name,
            'email_from_address' => $address,
            'email_driver' => $driver,
            'email_smtp_host' => $host,
            'email_port' => $port,
            'email_smtp_password' => $pass,
            'email_encryption_type' => $type,
            'mailgun_domain' => $mailgunDomain,
            'mailgun_api' => $mailgunApi,
            'mandrill_api' => $mandrill,
            'sparkpost_api' => $sparkpost
        );

        Setting::updateSettingData($emailData);

        if ($request->test_mail != '') {
            return $this->testMail($request->test_mail);
        }

        Cache::flush() && $this->cacheData();

        $response = [
            'message' => Lang::get('lang.email_settings') . ' ' . Lang::get('lang.successfully_saved')
        ];

        return response()->json($response, 200);
    }

    private function testMail($email)
    {
        $appName = Setting::getSettingValue('email_from_name')->setting_value;
        $sub = 'Test email';

        $emailHeader = '<html>
                           <div style="width: 35%; color: #333333; font-family: Helvetica; margin:auto; font-size: 125%; padding-bottom: 10px;">
                               <div style="text-align:center; padding-top: 10px; padding-bottom: 10px;">
                                   <h1>' . $appName . '</h1>
                               </div>
                               <div style="padding: 35px;padding-left:20px; border-bottom: 1px solid #cccccc; border-top: 1px solid #cccccc;">';

        $emailFooter = '        </div>
                           </div>
                       </html>';

        $text = $emailHeader . 'This is a test email' . $emailFooter;

        $eSend = new Email;

        if ($eSend->sendEmail($text, $email, $sub)) {
            return response()->json(['message' => Lang::get('lang.email_sent_and_settings_saved_successfully')]);
        }
    }

    public function emailSettingData()
    {
        Artisan::call('cache:clear');

        return $this->cacheData();
    }

    public function basicsetting()
    {
        if ($this->permissionCheck()->isAdmin()) {
            $apps = Setting::allData();

            return view('setting.basic_setting', ['item' => $apps]);
        } else {
            $response = [
                'msg' => Lang::get('lang.permission_error'),
                'template' => Lang::get('lang.permission_is_not_available')
            ];

            return response()->json($response, 401);
        }
    }

    public function basicSettingSave(Request $request)
    {

        $thousandSeparator = $request->thousand_separator;
        $decimalSeparator = $request->decimal_separator;

        $imageHandler = new imageHandler;
        $app_logo = '';
        $language_setting = $request->language_setting;

        $basicSetting = array(
            'time_format' => $request->time_format,
            'date_format' => $request->date_format,
            'time_zone' => $request->time_zone,
            'currency_symbol' => $request->currency_symbol,
            'currency_format' => $request->currency_format,
            'language_setting' => $language_setting,
            'number_of_decimal' => $request->number_of_decimal,
            'max_row_per_table' => $request->max_row_per_table,
            'app_name' => $request->app_name,
        );

        if ($thousandSeparator == $decimalSeparator) {
            $response = [
                'message' => Lang::get('lang.decimal_separator_and_thousand_separator_are_same')
            ];

            $status = 400;
        } else {
            $basicSetting['thousand_separator'] = $request->thousand_separator;
            $basicSetting['decimal_separator'] = $request->decimal_separator;
            $status = 200;
            $response = [
                'message' => Lang::get('lang.application_settings') . ' ' . Lang::get('lang.successfully_saved')
            ];
        }

        Setting::updateSettingData($basicSetting);
        if ($request->app_logo) {
            if ($request->app_logo == Config::get('app_logo')) {
                $app_logo = Config::get('app_logo');
            } else {
                if ($request->app_logo) {
                    $app_logo = $imageHandler->imageUpload($request->app_logo, 'logo_', 'uploads/logo/');
                }
            }
            if (Config::get('app_logo') != 'default-logo.png' && $request->app_logo != $app_logo) {
                unlink('uploads/logo/' . Config::get('app_logo'));
            }

            Setting::updateSetting('app_logo', $app_logo);
        }
        if ($request->background_image) {
            $File = new Filesystem;
            $path = 'images/background/default-background.jpg';
            if ($File->exists($path)) {
                unlink($path);
            }
            if ($file = $request->app_logo) {
                $imageHandler->imageUploadFixedName($request->background_image, 'default-background.jpg', 'images/background/');
            }
        }
        session()->put('language_setting', $language_setting);

        Artisan::call('cache:clear');
        Cache::flush() && $this->cacheData();

        return response()->json($response, $status);
    }

    public function basicSettingData()
    {
        return [
            'basicData' => $this->cacheData(),
            'language' => $this->getLanguageDirectory()
        ];
    }

    public function knowDefaultRowSettings()
    {
        Setting::getSettingData(['max_row_per_table']);
    }

    public function invoiceSettingsSave(Request $request)
    {
        $invoiceStartsFrom = $request->invoiceStartsFrom;
        $invoiceLogo = '';
        $allSettingsSaved = false;
        $invoiceSettingSaved = false;

        $currentInvoiceNumber = Setting::getSettingValue('last_invoice_number')->setting_value;

        if ($request->invoicePrefix || $request->invoiceSuffix || $request->autoGenerateInvoice || $request->autoEmailReceive) {
            $invoiceSettingData = [
                'invoice_prefix' => $request->invoicePrefix,
                'invoice_suffix' => $request->invoiceSuffix,
                'auto_generate_invoice' => $request->autoGenerateInvoice,
                'auto_email_receive' => $request->autoEmailReceive
            ];

            Setting::updateSettingData($invoiceSettingData);
            $allSettingsSaved = true;
        }

        if ($request->invoiceLogo) {
            if ($request->invoiceLogo == Config::get('invoiceLogo')) {
                $invoiceLogo = Config::get('invoiceLogo');
            } else {
                if ($file = $request->invoiceLogo) {
                    $imageHandler = new imageHandler;
                    $invoiceLogo = $imageHandler->imageUpload($request->invoiceLogo, 'logo_', 'uploads/logo/');
                }
            }

            if (Config::get('invoiceLogo') != 'default-logo.png' && file_exists(public_path() . '/uploads/logo/' . Config::get('invoiceLogo'))) {
                unlink(public_path() . '/uploads/logo/' . Config::get('invoiceLogo'));
            }

            Setting::updateSetting('invoiceLogo', $invoiceLogo);
            $allSettingsSaved = true;
        }

        if ($currentInvoiceNumber < $invoiceStartsFrom) {
            $invoiceStarts = [
                'invoice_starts_from' => $invoiceStartsFrom,
                'last_invoice_number' => $invoiceStartsFrom
            ];

            Setting::updateSettingData($invoiceStarts);
            $invoiceSettingSaved = true;

        }

        if ($allSettingsSaved && $invoiceSettingSaved) {
            $response = [
                'message' => Lang::get('lang.invoice_settings_small') . ' ' . Lang::get('lang.successfully_saved'),
            ];

            return response()->json($response, 200);
        }
        if ($allSettingsSaved && !$invoiceSettingSaved) {
            $response = [
                'message' => Lang::get('lang.invoice_number_already_in_use_but_others_settings_successfully_saved'),
            ];

            return response()->json($response, 200);
        }
    }

    public function getInvoiceSettings()
    {
        $invoiceSettingArray = [];
        $invoiceSettings = Setting::getSettingData([
            'invoice_prefix',
            'invoice_suffix',
            'invoice_starts_from',
            'auto_generate_invoice',
            'auto_email_receive'
        ]);

        foreach ($invoiceSettings as $rowSetting) {
            array_push($invoiceSettingArray, [$rowSetting->setting_name => $rowSetting->setting_value]);
        }
        return $invoiceSettingArray;
    }

    public function invoiceSettingData()
    {
        $invoiceSettings = Setting::getSettingData([
            'invoice_prefix',
            'invoice_suffix',
            'invoice_starts_from',
            'auto_generate_invoice',
            'auto_email_receive'
        ]);

        $currentInvoiceNumber = Setting::getSettingValue('last_invoice_number')->setting_value;

        $invoiceSettings = array_pluck($invoiceSettings->toArray(), 'setting_value', 'setting_name');

        return ['invoiceSettings' => $invoiceSettings, 'currentInvoiceNumber' => $currentInvoiceNumber];
    }

    public function getLanguageDirectory()
    {
        $dir = '../resources/lang';
        $files2 = array_diff(scandir($dir), array('..', '.', '.DS_Store'));

        return $files2;
    }

    public function productSetting()
    {
        $reOrder = Setting::getSettingValue('re_order')->setting_value;
        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;
        return ['reOrder' => $reOrder, 'skuPrefix' => $skuPrefix];
    }

    public function productSettingSave(Request $request)
    {
        $reOrder = $request->reOrder;
        $skuPrefix = $request->skuPrefix;
        Setting::updateSetting('sku_prefix', $skuPrefix);
        if (empty($reOrder)) {
            return response()->json([
                'success' => false,
                'message' => Lang::get('lang.field_should_not_empty')
            ]);
        } else {
            Setting::updateSetting('re_order', $reOrder);
        }
        return response()->json([
            'success' => true,
            'message' => Lang::get('lang.product_settings_saved_successfully')
        ]);
    }

    public function storeKeyboardShortcutSettings(Request $request)
    {
        $user_id = Auth::user()->id;
        $data = [];
        $data['customShortcuts'] = serialize($request->shortcut);
        $data['created_by'] = $user_id;

        if ($request->shortcutStatus == true) $data['shortcutsStatus'] = 1;
        else $data['shortcutsStatus'] = 0;

        $isUpdated = ShortcutKey::updateShortcutSettings($user_id, $data);

        if ($isUpdated == 1) {
            $response = [
                'message' => Lang::get('lang.keyboard_shortcut_settings_updated_successfully'),
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.something_wrong'),
            ];
            return response()->json($response, 400);
        }
    }

    public function getShortcutSettings($id)
    {
        $userId = Auth::user()->id;
        return ShortcutKey::getShortcutSettings($userId);
    }
}
