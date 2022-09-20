<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Models\Branch;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Config;

class InvoiceTemplateController extends Controller
{
    public function index(Request $request)
    {
        $data = InvoiceTemplate::getInvoiceTemplate($request);
        $totalCount = InvoiceTemplate::countData();

        return ['datarows' => $data, 'count' => $totalCount];
    }

    public function getAllInvoiceTemplate()
    {
        return InvoiceTemplate::allData();
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required'
        ]);

        InvoiceTemplate::create([
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'custom_content' => $request->input('content'),
        ]);
    }

    public function show($id)
    {
        return InvoiceTemplate::getOne($id);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required',
        ]);

        $success = InvoiceTemplate::updateData($id, [
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'custom_content' => $request->input('content')
        ]);

        $msg = Lang::get('lang.invoice_setting_saved_successfully');
        $status = 200;

        if (!$success) {

            $msg = Lang::get('lang.error_during_update');
            $status = 404;
        }

        return response()->json(['message' => $msg], $status);
    }

    public function getInvoiceTemplateToPrint($orderId, $cashRegisterId, $orderType,$from)
    {
        $data = InvoiceTemplate::getInvoiceTemplateToPrint($cashRegisterId, $orderType);

        $orderDetails = Order::getOrderDetailsForInvoice($orderId, $orderType);

        $discountAmount = OrderItems::getDiscountAmount($orderId);

        $itemDetails = $this->getItemDetailsforInvoice($orderId);
        $paymentDetails = $this->makePaymentDetailsForInvoice($orderId);

        $appName = Config::get('app_name');
        $invoiceLogo = Config::get('invoiceLogo');

        $publicPath = \Request::root();
        $src = $publicPath . '/uploads/logo/'.$invoiceLogo;
        if($from == 'email'){
            $logo = '<div style="text-align: center;width: 100%;">
                    <img class="invoice-logo" style=" max-width: 200px;left:45%;position:relative;text-align: center; height: auto;" src= "' . $src . '" alt="Logo">
                </div>';
        }else{
            $logo = '<div>
                   <img class="invoice-logo" style=" max-width: 200px; height: auto; margin: 0 auto;" src= "' . $src . '" alt="Logo">
               </div>';
        }


        $allSettingFormat = new AllSettingFormat;
        
        //customer
        if ($orderDetails->customer_name == null) {
            $orderDetails->customer_name = Lang::get('lang.walk_in_customer');
        }

        $replace = array(
            '{app_name}' => $appName,
            '<p>{app_logo}</p>' => $logo,
            '{invoice_id}' => $orderDetails->invoice_id,
            '{employee_name}' => $orderDetails->employee_name,
            '{date}' => $allSettingFormat->getDate($orderDetails->date),
            '{time}' => $allSettingFormat->timeFormat($orderDetails->created_at),

            '<tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>' => $itemDetails,

            '<tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>' => $paymentDetails,
            '{sub_total}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->sub_total)),
            '{vat}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->total_tax)),

            '{total}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->total)),
            '{exchange}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->exchange)),
        );

        if ($discountAmount != null) {
            $replace['{discount}'] = $allSettingFormat->getCurrency($allSettingFormat->thousandSep($discountAmount->overAllDiscount));
        } else {
            $replace['{discount}'] = $allSettingFormat->getCurrency($allSettingFormat->thousandSep(0.00));
        }

        if ($orderType == 'sales') {
            $replace['{customer_name}'] = $orderDetails->customer_name;
        } else {
            if ($orderDetails->supplier_name == null) {
                $orderDetails->supplier_name = Lang::get('lang.walk_in_supplier');
            }
            $replace['{supplier_name}'] = $orderDetails->supplier_name;
        }

        return (['data' => strtr($data, $replace)]);
    }

    private function makePaymentDetailsForInvoice($orderId)
    {
        $allSettingFormat = new AllSettingFormat;
        $paymentDetails = Payments::getPaymentDetails($orderId);

        $row = "";

        foreach ($paymentDetails as $item) {

            $newRow = '<tr style="text-align: left;">
                    <th style="padding: 7px 0;">' . $item['name'] . '</th>
                    <th style="padding: 7px 0;"></th>
                    <th style="padding: 7px 0;"></th>
                    <th style="padding: 7px 0;"></th>
                    <td style="padding: 7px 0; text-align: right;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['paid'])) . '</td>
                </tr>';
            $row = $row . $newRow;
        }

        return $row;
    }

    private function getItemDetailsforInvoice($orderId)
    {
        $allSettingFormat = new AllSettingFormat;
        $itemDetails = OrderItems::getItemDetailsforInvoice($orderId);

        $row = "";

        foreach ($itemDetails as $item) {

            if ($item['variant_title'] == 'default_variant') {
                $item['variant_title'] = '';
            } else {
                $item['variant_title'] = " ( " . $item['variant_title'] . " ) ";
            }
            $newRow = '<tr>
                    <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $item['title'] . $item['variant_title'] . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->thousandSep($item['quantity']) . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['price'])) . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->thousandSep($item['discount']) . '%</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['sub_total'])) . '</td>
                </tr>';
            $row = $row . $newRow;
        }

        return $row;
    }
}
