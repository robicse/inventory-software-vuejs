@inject('permission', 'App\Http\Controllers\API\PermissionController')
@inject('appLogo', 'App\Http\Controllers\API\SettingController')

<?php
$data = "$_SERVER[REQUEST_URI]";

?>
<side-bar
        route={{$data}}
                sales={{$permission->salesManagePermission()}}
                suppliers={{$permission->salesManagePermission()}}
                receives={{$permission->receivesManagePermission()}}
                products={{$permission->productManagePermission()}}
                product_category={{$permission->productCategoryManagePermission()}}
                product_brand={{$permission->productBrandManagePermission()}}
                product_group={{$permission->productGroupManagePermission()}}
                units={{$permission->productUnitManagePermission()}}
                variant_attributes={{$permission->productVariantManagePermission()}}
                sales_report={{$permission->salesReportPermission()}}
                receiving_report={{$permission->receivingReportPermission()}}
                register_report={{$permission->registerReportPermission()}}
                inventory_report={{$permission->inventoryReportPermission()}}
                customers={{$permission->customersManagePermission()}}
                customer_group={{$permission->customerGroupManagePermission()}}
                applogo={{ $appLogo->getAppLogo() }}
                appsettings={{ $permission->appsManagePermission() }}
                emailsettings={{ $permission->emailsManagePermission() }}
                emailtemplate={{ $permission->emailTemplateManagePermission () }}
                tax_settings={{$permission->taxManagePermission()}}
                payment_settings={{$permission->paymentManagePermission()}}
                sales_channels={{$permission->salesChannelManagePermission()}}
                branches_setting={{$permission->branchsManagePermission()}}
                invoice_settings={{$permission->InvoiceSettingsPermission()}}
                users={{$permission->userManagePermission()}}
                roles={{$permission->rolesManagePermission()}}
                cash_register={{$permission->cashRegistersManagePermission()}}

>

</side-bar>
