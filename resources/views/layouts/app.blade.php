@include('layouts.include.head')
<body>

@inject('appConfig', 'App\Http\Controllers\Controller')

<div id="app">

    @if(Auth::user())
        @include('layouts.include.navbar')
        @include('layouts.include.sidebar')
    @endif
    <main id="app">

        @yield('content')

    </main>
</div>

<script>
    window.appConfig = {
        appUrl: "<?php echo $appConfig->appUrl ?>",
        publicPath: "<?php echo $appConfig->publicPath ?>",
        dateFormat: "<?php echo Config::get('dateFormat') ?>",
        defaultRowSetting: "<?php echo Config::get('max_row_per_table') ?>",
        currencySymbol: "<?php echo Config::get('currency_symbol') ?>",
        currencyFormat: "<?php echo Config::get('currency_format') ?>",
        thousandSeparator: "<?php echo Config::get('thousand_separator') ?>",
        decimalSeparator: "<?php echo Config::get('decimal_separator') ?>",
        numDec: "<?php echo Config::get('number_of_decimal') ?>",
        timeFormat: "<?php echo Config::get('time_format') ?>",
        shortcutStatus: "<?php echo Config::get('overAllShortcutStatus') ?>",
        shortcutKeyInfo: {
            loadSalesPage: {
                shortCutKey: "<?php echo Config::get('loadSalesPage') ?>",
                status: "<?php echo Config::get('loadSalesPage_status') ?>",
            },
            productSearchShortcut: {
                shortCutKey: "<?php echo Config::get('productSearch') ?>",
                status: "<?php echo Config::get('productSearch_status') ?>",
            },
            holdCardShortcut: {
                shortCutKey: "<?php echo Config::get('holdCard') ?>",
                status: "<?php echo Config::get('holdCard_status') ?>",
            },
            payShortcut: {
                shortCutKey: "<?php echo Config::get('pay') ?>",
                status: "<?php echo Config::get('pay_status') ?>",
            },
            addCustomerShortcut: {
                shortCutKey: "<?php echo Config::get('addCustomer') ?>",
                status: "<?php echo Config::get('addCustomer_status') ?>",
            },
            cancelCardShortcut: {
                shortCutKey: "<?php echo Config::get('cancelCarditem') ?>",
                status: "<?php echo Config::get('cancelCarditem_status') ?>",
            },
            donePayment: {
                shortCutKey: "<?php echo Config::get('donePayment1') ?>",
                status: "<?php echo Config::get('donePayment1_status') ?>",
            }
        }
    }
</script>

@include('layouts.include.footer')