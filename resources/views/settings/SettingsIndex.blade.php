@extends('layouts.app')

@section('title', trans("lang.settings"))

@section('content')
    @inject('perm', 'App\Http\Controllers\API\PermissionController')
    <setting-index
            app_settings={{$perm->appsManagePermission()}}
            email_settings={{$perm->emailsManagePermission()}}
            email_templates={{$perm->emailTemplateManagePermission()}}
            payment_settings={{$perm->paymentManagePermission()}}
            tax_settings={{$perm->taxSettingManagePermission()}}
            branches_setting={{$perm->branchsManagePermission()}}
            branches_setting={{$perm->branchsManagePermission()}}
            roles={{$perm->rolesManagePermission()}}
            users={{$perm->userManagePermission()}}
            cash_register={{$perm->cashRegistersManagePermission()}}
            invoice_settings={{$perm->InvoiceSettingsPermission()}}
            invoice_templates={{$perm->InvoiceTemplateSettingsPermission()}}
            product_settings={{$perm->productSettingsPermission()}}
            {{--updates_setting={{$perm->updateSettingPermission()}}--}}
            tab_name={{$tab_name}}
            route_name={{$route_name}}></setting-index>

@endsection
