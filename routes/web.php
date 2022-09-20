<?php

//home page

Route::get('downloadSample', 'API\ProductsController@downloadProductSample');

// Auth Route
Route::get('/', '\App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::get('/login', '\App\Http\Controllers\Auth\LoginController@showLoginForm');
Route::post('/', ['as' => '/', 'uses' => '\App\Http\Controllers\Auth\LoginController@login']);
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('/recover', 'AuthController@recover');
Route::post('/password/reset/{token}', 'Auth\ResetPasswordController@reset');
Route::get('/verify/{token}', 'Auth\RegisterController@verifyUser');
Route::get('accept/{token}', 'API\InviteController@accept');
Route::get('register/{token}', 'Auth\RegisterController@regForm');
Route::post('register/{token}', 'API\InviteController@invitedRegistration');

// Auth middleware group Route
Route::group(['middleware' => 'auth'], function () {

    //logout route
    Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
    //Route::get('/dashboardData', 'DashboardController@index');

    //dashboard routes starts
    Route::get('/getAllDashboardData', 'API\DashboardController@getAllData');

    // dashboard controller routes ends
    Route::get('/dashboard', 'API\DashboardController@index')->name('dashboard');
    Route::get('/dashboardgetdata', 'API\DashboardController@getData');

    // Profile Route
    Route::get('myprofile', 'API\ProfileController@myindex');
    Route::get('user-profile', 'API\ProfileController@index');
    Route::post('profile/{id}', 'API\ProfileController@update');
    Route::post('/updatePassword/{id}', 'API\ProfileController@updatepassword');
    Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
    Route::get('getDateFormat', 'API\ProfileController@dateFormat');

    // Email template Route
    Route::post('templatelist', 'API\EmailTemplateController@index');
    Route::get('/gettemplatecontent/{id}', 'API\EmailTemplateController@show')->middleware('permissions:can_edit_email_template');
    Route::post('/setcustomcontent/{id}', 'API\EmailTemplateController@update')->middleware('permissions:can_edit_email_template');
    Route::get('/knowDefaultRowSettings', 'API\SettingController@knowDefaultRowSettings');

    // Email Setting Route
    Route::get('/emailsetting', 'API\SettingController@emailSettingForm');
    Route::post('/emailsetting', 'API\SettingController@emailSettingSave')->middleware('permissions:can_edit_email_setting');
    Route::get('/emailsettingdata', 'API\SettingController@emailSettingData')->middleware('permissions:can_edit_email_setting');

    // View Route
    Route::get('/settings', 'API\SettingController@index');

    // App Setting Route
    Route::post('/basic-setting', 'API\SettingController@basicSettingSave')->middleware('permissions:can_edit_application_setting');
    Route::get('/basicsettingdata', 'API\SettingController@basicSettingData')->middleware('permissions:can_edit_application_setting');
    Route::get('timezone', 'API\ProfileController@getTimezone');

    // Invoice Settings Route
    Route::post('invoice-setting-save', 'API\SettingController@invoiceSettingsSave')->middleware('permissions:can_manage_invoice_setting');
    Route::get('invoice-settings', 'API\SettingController@getInvoiceSettings')->middleware('permissions:can_manage_invoice_setting');
    Route::get('invoice-setting-data', 'API\SettingController@invoiceSettingData')->middleware('permissions:can_manage_invoice_setting');

    // Invoice Template
    Route::post('invoice-templates', 'API\InvoiceTemplateController@index');
    Route::get('/get-invoice-template/{id}', 'API\InvoiceTemplateController@show');
    Route::post('/save-invoice-template/{id}', 'API\InvoiceTemplateController@update');
    Route::post('/add-invoice-template', 'API\InvoiceTemplateController@store');
    Route::get('/allInvoice', 'API\InvoiceTemplateController@getAllInvoiceTemplate');

    // Invite Route
    Route::get('/invite', 'API\InviteController@invite');
    Route::post('/invite', 'API\InviteController@process')->middleware('permissions:can_manage_users');
    Route::get('/allroleid', 'API\InviteController@getRoleId');

    // Role Route
    Route::post('roles-list', 'API\RoleController@getRolesList');
    Route::get('/roletitle', 'API\RoleController@allData');
    Route::post('/addrole', 'API\RoleController@store')->middleware('permissions:can_manage_roles');
    Route::post('/addrole/{id}', 'API\RoleController@update')->middleware('permissions:can_manage_roles');
    Route::get('/rolepermissions/{id}', 'API\RoleController@getRolePermissions');
    Route::get('/rolepermissions/', 'API\RoleController@getRoleWithout');
    Route::post('/deleterole/{id}', 'API\RoleController@delete')->middleware('permissions:can_manage_roles');
    Route::get('roles', 'API\RoleController@index');

    //Product setting route
    Route::get('/product-setting','API\SettingController@productSetting');
    Route::post('/product-setting-save','API\SettingController@productSettingSave');

    // Notification Route
    Route::get('notify', 'API\NotificationController@index');
    Route::post('/upnotify/{id}', 'API\NotificationController@update');
    Route::get('count', 'API\NotificationController@count');
    Route::post('countup/{id}', 'API\NotificationController@countUp');
    Route::get('booking/{id}', 'API\NotificationController@singleView');
    Route::get('notification', 'API\NotificationController@allNoti');
    Route::get('notifications', 'API\NotificationController@reorder');

    //all users
    Route::get('users', 'API\UserController@allusers');
    Route::post('/roleassign/{id}', 'API\RoleAssignController@update');
    Route::get('/get-user/{id}', 'API\UserController@getRowUser');
    Route::post('/enable-disable-user/{id}', 'API\UserController@enableUser');
    Route::post('/make-admin-user/{id}', 'API\UserController@newAdminUser');

    //product module
    Route::group(['prefix' => 'products', 'as' => 'products'], function () {
        // Index Page
        Route::get('/', 'API\ProductsController@index');

        // Products
        Route::post('products', 'API\ProductsController@getProduct');
        Route::post('store', 'API\ProductsController@storeProduct')->middleware('permissions:can_manage_products');
        Route::post('delete/{id}', 'API\ProductsController@deleteProduct')->middleware('permissions:can_manage_products');
        Route::get('all-supporting-data', 'API\ProductsController@productSupportingData');
        Route::get('edit-product/{id}', 'API\ProductsController@getProductEditData');
        Route::post('edit/{id}', 'API\ProductsController@editProduct');
        Route::get('details/{id}', 'API\ProductsController@productDetailsShow');
        Route::get('getDetails/{id}', 'API\ProductsController@getProductDetails');
        Route::post('variantDetails/{id}', 'API\ProductsController@getVariantDetails');
        Route::post('/import', 'API\ProductsController@importProduct')->middleware('permissions:can_manage_products');
        Route::post('/import-stock', 'API\ProductsController@importOpeningStock')->middleware('permissions:can_manage_products');
        Route::get('/supporting-data', 'API\ProductsController@getSupportingData');

        // Variants
        Route::get('variant/{id}', 'API\ProductsController@showVariant');

        // Product Category
        Route::get('category', 'API\ProductCategoriesController@index');
        Route::post('categories', 'API\ProductCategoriesController@getCategory');
        Route::post('category/store', 'API\ProductCategoriesController@storeCategory')->middleware('permissions:can_manage_categories');
        Route::post('category/delete/{id}', 'API\ProductCategoriesController@deleteCategory')->middleware('permissions:can_manage_categories');
        Route::get('category/{id}', 'API\ProductCategoriesController@showCategory');
        Route::post('category/{id}', 'API\ProductCategoriesController@updateCategory')->middleware('permissions:can_manage_categories');

        // Product Brand
        Route::get('brand', 'API\ProductBrandsController@index');
        Route::post('brands', 'API\ProductBrandsController@getBrand');
        Route::post('brand/store', 'API\ProductBrandsController@storeBrand')->middleware('permissions:can_manage_brands');
        Route::post('brand/delete/{id}', 'API\ProductBrandsController@deleteBrand');
        Route::get('brand/{id}', 'API\ProductBrandsController@showBrand');
        Route::post('brand/{id}', 'API\ProductBrandsController@updateBrand')->middleware('permissions:can_manage_brands');

        // Product Group
        Route::get('group', 'API\ProductGroupsController@getGroup');
        Route::post('groups', 'API\ProductGroupsController@getAllGroup');
        Route::post('group/store', 'API\ProductGroupsController@storeGroup')->middleware('permissions:can_manage_groups');
        Route::post('group/delete/{id}', 'API\ProductGroupsController@deleteGroup')->middleware('permissions:can_manage_groups');
        Route::get('group/{id}', 'API\ProductGroupsController@showGroup');
        Route::post('group/{id}', 'API\ProductGroupsController@updateGroup')->middleware('permissions:can_manage_groups');
        Route::delete('group/delete/{id}', 'API\ProductGroupsController@deleteGroup')->middleware('permissions:can_manage_groups');

        // Product Attribute
        Route::get('attribute', 'API\ProductAttributesController@getAttribute');
        Route::post('variant-attributes', 'API\ProductAttributesController@getAttributeList');
        Route::get('product-variant-attribute', 'API\ProductAttributesController@getProductAttributeList');
        Route::post('attribute/store', 'API\ProductAttributesController@storeAttribute')->middleware('permissions:can_manage_variant_attribute');
        Route::post('attribute/delete/{id}', 'API\ProductAttributesController@deleteAttribute')->middleware('permissions:can_manage_variant_attribute');
        Route::get('attribute/{id}', 'API\ProductAttributesController@showAttribute');
        Route::post('attribute/{id}', 'API\ProductAttributesController@updateAttribute')->middleware('permissions:can_manage_variant_attribute');

        // Product Units
        Route::post('unit/store', 'API\ProductUnitsController@store');
        Route::post('units', 'API\ProductUnitsController@getUnit');
        Route::post('unit/{id}', 'API\ProductUnitsController@update');
        Route::get('unit/{id}', 'API\ProductUnitsController@show');
        Route::post('unit/delete/{id}', 'API\ProductUnitsController@delete');

    });

    //Sales
    Route::get('sales', 'API\SalesController@index');
    Route::post('sales-product', 'API\SalesController@getProduct');
    Route::post('get-return-orders', 'API\SalesController@getReturnProduct');
    Route::post('sales-returns-type-set', 'API\SalesController@setSalesReturnsType');
    Route::post('sales-receiving-type-set', 'API\SalesController@setSalesReceivingType');
    Route::post('/store', 'API\SalesController@salesStore')->middleware('permissions:can_manage_sales');
    Route::post('/continue-sale', 'API\SalesController@salesStore')->middleware('permissions:can_manage_sales');
    Route::post('/continue-sale-payments', 'API\SalesController@getPaymentsAndDetails');
    Route::get('/get-hold-orders', 'API\SalesController@getHoldOrder');
    Route::post('customers-list', 'API\SalesController@customerList');
    Route::post('sales-branch-set', 'API\SalesController@setBranch');
    Route::post('sales-cancel', 'API\SalesController@salesCancel');
    Route::get('/receives', 'API\SalesController@orderReceive');
    Route::get('/get-register-amount/{id}', 'API\SalesController@getRegisterAmount');
    Route::post('/save-due-amount', 'API\SalesController@saveDueAmount');

    Route::post('/sale/delete/{id}', 'API\SalesController@deleteSale')->middleware('permissions:can_manage_sales');


    //tax
    Route::post('/taxlist', 'API\TaxController@getData');
    Route::get('/taxlist', 'API\TaxController@taxGetDate');
    Route::post('/addtax', 'API\TaxController@store')->middleware('permissions:can_manage_tax_settings');
    Route::post('/edittax/{id}', 'API\TaxController@update')->middleware('permissions:can_manage_tax_settings');
    Route::get('/edittax/{id}', 'API\TaxController@getRowTax');
    Route::post('/deletetax/{id}', 'API\TaxController@deleteTax')->middleware('permissions:can_manage_tax_settings');

    //branches
    Route::get('/allBranches', 'API\BranchController@getAllBranches');
    Route::get('/branches', 'API\BranchController@index');
    Route::post('/branches', 'API\BranchController@getBranchList');
    Route::get('/branch-list', 'API\BranchController@branchList');
    Route::post('/addbranch', 'API\BranchController@store')->middleware('permissions:can_manage_branches');
    Route::post('/editbranch/{id}', 'API\BranchController@update')->middleware('permissions:can_manage_branches');
    Route::get('/edit-branch/{id}', 'API\BranchController@getRowBranch');
    Route::post('/delete-branch/{id}', 'API\BranchController@deleteBranch')->middleware('permissions:can_manage_branches');


    //contacts
    Route::get('/contacts', 'API\ContactController@index');

    // customers
    Route::get('/customers', 'API\CustomerController@index');
    Route::post('import-customer-contacts', 'API\CustomerController@importCustomerContacts');

    ///suppliers
    Route::post('import-supplier-contacts', 'API\SupplierController@importSuppilerContacts');
    Route::get('/suppliers', 'API\SupplierController@index');
    Route::post('supplier/store', 'API\SupplierController@store');
    Route::post('/supplier-list', 'API\SupplierController@getSupplierData');
    Route::get('/supplier-edit/{id}', 'API\SupplierController@getData');
    Route::get('/supplier/{id}', 'API\SupplierController@getSupplierDetails');
    Route::post('supplier/{id}', 'API\SupplierController@editSupplierData');
    Route::post('supplier/delete/{id}', 'API\SupplierController@deleteSupplier');
    Route::post('supplier-delivery-report/{id}', 'API\SupplierController@getSupplierDeliveryRecords');
    Route::post('/update-supplier-avatar/{id}', 'API\SupplierController@updateAvatar');

    //show customer list
    Route::post('/customer-list', 'API\CustomerController@getCustomerList');

    //save customer data
    Route::post('/customer/store', 'API\CustomerController@store')->middleware('permissions:can_manage_customers');
    Route::post('/customer/{id}', 'API\CustomerController@updateCustomer')->middleware('permissions:can_manage_customers');
    Route::post('/delete/{id}', 'API\CustomerController@destroy')->middleware('permissions:can_manage_customers');
    Route::post('/customer/delete/{id}', 'API\CustomerController@deleteCustomer')->middleware('permissions:can_manage_customers');
    Route::get('/customer/{id}', 'API\CustomerController@getCustomerDetails');
    Route::get('/customer-data/{id}', 'API\CustomerController@getCustomerData');
    Route::post('/update-customer-avatar/{id}', 'API\CustomerController@updateAvatar');

    // groups
    Route::get('/groups', 'API\CustomerGroupController@index');
    Route::post('/groups', 'API\CustomerGroupController@getGroups');
    Route::post('/group/store', 'API\CustomerGroupController@store')->middleware('permissions:can_manage_customer_groups');
    Route::get('/groups/{id}', 'API\CustomerGroupController@show');
    Route::post('/group/delete/{id}', 'API\CustomerGroupController@destroy')->middleware('permissions:can_manage_customer_groups');
    Route::post('/group/{id}', 'API\CustomerGroupController@update')->middleware('permissions:can_manage_customer_groups');
    Route::get('/customer-groups', 'API\CustomerGroupController@getCustomerGroups');

    //payment
    Route::get('/paymentlist', 'API\PaymentController@getData');
    Route::post('/payment-list', 'API\PaymentController@getPaymentList');
    Route::post('/addpayment', 'API\PaymentController@store')->middleware('permissions:can_manage_payment_settings');
    Route::post('/editpayment/{id}', 'API\PaymentController@update')->middleware('permissions:can_manage_payment_settings');
    Route::post('/delete-payment/{id}', 'API\PaymentController@deletePaymentMethod')->middleware('permissions:can_manage_payment_settings');
    Route::get('/payment-details/{id}', 'API\PaymentController@getPaymentDetailsData');
    Route::get('/invoice-logo', 'API\PaymentController@getInvoiceLogo');
    Route::get('/get-auto-invoice', 'API\PaymentController@getAutoInvoice');

    //users
    Route::post('/users-list', 'API\UserController@getUsersList');
    Route::get('/user/{id}', 'API\UserController@userDetail');
    Route::get('/userChartData/{id}', 'API\UserController@getUser');

    //sales register
    Route::get('/cash-registers', 'API\CashRegisterController@getCashRegisterList');
    Route::post('cash-registers', 'API\CashRegisterController@index');
    Route::post('cash-register-store', 'API\CashRegisterController@store')->middleware('permissions:can_manage_cash_registers');
    Route::get('cash-register-show/{id}', 'API\CashRegisterController@show');
    Route::post('cash-register-update/{id}', 'API\CashRegisterController@update')->middleware('permissions:can_manage_cash_registers');
    Route::post('delete-register/{id}', 'API\CashRegisterController@deleteCashRegister')->middleware('permissions:can_manage_cash_registers');
    Route::post('cash-register-open-close', 'API\CashRegisterController@cashRegisterLogs');

    //Keyboard shortcuts
    Route::post('shortcuts', 'API\SettingController@storeKeyboardShortcutSettings');
    Route::get('shortcut-setting-data/{id}', 'API\SettingController@getShortcutSettings');
    Route::post('tax-report', 'API\ReportController@taxReports');
    Route::post('profit-loss-report', 'API\ReportController@profitLossReport');

    // Report Route
    Route::get('reports', 'API\ReportController@index');
    Route::get('dateRangeAFormat', 'API\ReportController@getdateRangeAFormat');
    Route::post('customer-purchase-report/{id}', 'API\ReportController@customerPurchaseReport');
    Route::get('reports/ordersDetails/{id}', 'API\ReportController@getOrdersDetails');

    //sales
    Route::post('sales-report', 'API\ReportController@salesReport');
    Route::post('sales-summary-report', 'API\ReportController@salesSummaryReport');
    Route::get('reports/sales/{id}', 'API\ReportController@salesReportsDetails');
    Route::post('reports/salesDetails/{id}', 'API\ReportController@getSalesDetails');
    Route::post('internal-sales-branch', 'API\BranchController@getInternalSalesBranch');
    // custom
    Route::get('reports/OrderEditData/{id}', 'API\ReportController@getOrderDataDetails');
    //Route::post('/reports/updateOrderDetails', 'API\ReportController@store')->middleware('permissions:can_manage_customers');
    Route::post('/reports/updateOrderDetails', 'API\ReportController@updateOrderDetails');
    Route::get('reports/stockInEditData/{id}', 'API\ReportController@getStockInProductDetails');

    //receiving
    Route::post('receiving-summary-report', 'API\ReportController@receivingSummary');
    Route::post('receiving-report', 'API\ReportController@receivingReport');
    Route::get('reports/receiving/{id}', 'API\ReportController@receivingReportsDetails');

    //register log
    Route::post('register-log-reports', 'API\ReportController@registerLogReports');
    Route::get('cash-register-for-filter', 'API\ReportController@getCashRegisterFilterData');

    //inventory
    Route::post('inventory-reports', 'API\ReportController@inventoryReports');
    Route::get('inventory-reports-filter', 'API\ReportController@inventoryReportsFilter');//new inventory filter

    //payment
    Route::post('payment-reports', 'API\ReportController@paymentReport');
    Route::get('payment-reports-filter', 'API\ReportController@paymentReportFilter'); // new report filter
    Route::get('payment-summery-reports-filter', 'API\ReportController@paymentSummaryReportFilter');
    Route::post('payment-summary-reports', 'API\ReportController@paymentSummary');

    // Sales Chart
    Route::post('yearly-sales-chart', 'API\ReportController@yearlySalesChart');
    Route::get('branch-user', 'API\ReportController@getBranchAndUser');
    Route::get('available-stock-chart', 'API\ReportController@availableStockChart');

    Route::post('receiving-summary-reports', 'API\ReportController@receivingSummary');


    // cash register log
    Route::get('cash-register-logs', 'API\RegisterLogController@index');
    Route::post('save-cash-register', 'API\RegisterLogController@saveRegisterLog');

    // Updates Route
    Route::get('/gain-update', 'API\UpdateController@applicationVersion');
    Route::get('/update-version-list', 'API\UpdateController@versionUpdateList');
    Route::post('/install-new-version/{version}', 'API\UpdateController@updateAction');
    Route::get('/update-list', 'API\UpdateController@updateAppUrl');
    Route::get('/curl_get_contents', 'API\UpdateController@curl_get_contents');
    Route::get('/nexInstallVersion', 'API\UpdateController@nexInstallVersion');

    //Clear language cache
    Route::get('/clear-language-cache', function () {
        Artisan::call('cache:clear');
    });

});

// Localization
Route::get('/js/lang.js', function () {
    $strings = Cache::rememberForever('lang.js', function () {
        $lang = config('app.locale');
        $files = glob(resource_path('lang/' . $lang . '/*.php'));
        $strings = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if ($name !== "lang") {
                $new_keys = require $file;
                $strings = array_merge($strings, $new_keys);
            }
        }
        return $strings;
    });
    header('Content-Type: text/javascript');
    echo('window.i18n = ' . json_encode(array("lang" => $strings)) . ';');
    exit();
})->name('assets.lang');