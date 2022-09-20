<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $purchase_code = session('purchase_code');
        DB::table('settings')->insert([
            ['setting_name'     => 'time_format',                   'setting_value'   => '12h'],
            ['setting_name'     => 'date_format' ,                  'setting_value'   => 'Y-m-d'],
            ['setting_name'     => 'currency_symbol' ,              'setting_value'   => '$'],
            ['setting_name'     => 'currency_format' ,              'setting_value'   => 'left'],
            ['setting_name'     => 'thousand_separator' ,           'setting_value'   => ','],
            ['setting_name'     => 'language_setting' ,             'setting_value'   => 'english'],
            ['setting_name'     => 'decimal_separator' ,            'setting_value'   => '.'],
            ['setting_name'     => 'number_of_decimal' ,            'setting_value'   => '2'],
            ['setting_name'     => 'offday_setting' ,               'setting_value'   => ''],
            ['setting_name'     => 'email_from_name' ,              'setting_value'   => ''],
            ['setting_name'     => 'email_from_address' ,           'setting_value'   => ''],
            ['setting_name'     => 'email_driver' ,                 'setting_value'   => ''],
            ['setting_name'     => 'email_smtp_host' ,              'setting_value'   => ''],
            ['setting_name'     => 'email_port' ,                   'setting_value'   => ''],
            ['setting_name'     => 'email_smtp_password' ,          'setting_value'   => ''],
            ['setting_name'     => 'email_encryption_type' ,        'setting_value'   => ''],
            ['setting_name'     => 'mandrill_api' ,                 'setting_value'   => ''],
            ['setting_name'     => 'sparkpost_api' ,                'setting_value'   => ''],
            ['setting_name'     => 'mailgun_domain' ,               'setting_value'   => ''],
            ['setting_name'     => 'mailgun_api' ,                  'setting_value'   => ''],
            ['setting_name'     => 'max_row_per_table' ,            'setting_value'   => '10'],
            ['setting_name'     => 'app_name' ,                     'setting_value'   => 'Gain POS'],
            ['setting_name'     => 'app_logo' ,                     'setting_value'   => 'default-logo.png'],
            ['setting_name'     => 'currency_code' ,                'setting_value'   => 'usd'],
            ['setting_name'     => 'purchase_code' ,                'setting_value'   => $purchase_code],
            ['setting_name'     => 'can_signup' ,                   'setting_value'   => '1'],
            ['setting_name'     => 'can_login' ,                    'setting_value'   => '1'],
            ['setting_name'     => 'invoice_prefix' ,               'setting_value'   => ''],
            ['setting_name'     => 'invoice_suffix' ,               'setting_value'   => ''],
            ['setting_name'     => 'last_invoice_number' ,          'setting_value'   => 1],
            ['setting_name'     => 'auto_generate_invoice' ,        'setting_value'   => 0],
            ['setting_name'     => 'auto_email_receive' ,           'setting_value'   => 0],
            ['setting_name'     => 'invoice_starts_from' ,          'setting_value'   => 1],
            ['setting_name'     => 'invoiceLogo' ,                  'setting_value'   => 'default-logo.jpg'],
            ['setting_name'     => 're_order' ,                     'setting_value'   => 10],
            ['setting_name'     => 'sku_prefix' ,                   'setting_value'   => ''],
            ['setting_name'     => 'sales_return_status' ,          'setting_value'   => 'sales'],
        ]);
    }
}

