<?php

namespace App\Http\Controllers\Api;

use App\Libraries\Permissions;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\User;

class PermissionController extends Controller
{
    public function permissionCheck()
    {
        $controller = new Permissions;

        return $controller;
    }
    public function salesManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_sales') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }
    public function salesPriceManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_sales_price') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }
    public function suppliersManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_add_suppliers') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_suppliers')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }
    public function receivesManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_receives') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }
    public function userManagePermission()
    {
       if ($this->permissionCheck()->hasPermission('can_manage_users') || $this->permissionCheck()->isAdmin()) {
            return 1;
       }else {
            return 0;
       }
    }
    public function appsManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_edit_application_setting') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function emailsManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_edit_email_setting') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function emailTemplateManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_edit_email_template') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }


    public function paymentManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_payment_settings') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function salesChannelManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_sales_channels') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function taxManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_tax_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function taxSettingManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_tax_settings') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function branchsManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_branches') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function rolesManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_roles') || $this->permissionCheck()->isAdmin()) {
            return 1;
        }else{
            return 0;
        }
    }

    public function cashRegistersManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_cash_registers') || $this->permissionCheck()->isAdmin()) {
            return 1;
        }else{
            return 0;
        }
    }

    public function customersManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_customers') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_customers')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function customerGroupManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_customer_groups') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_customer_groups')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function InvoiceSettingsPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_invoice_setting') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    // Need to implement for user

    public function InvoiceTemplateSettingsPermission()
    {
        if ($this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function salesReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_sales_reports') || $this->permissionCheck()->hasPermission('can_see_personal_sales_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function salesSummaryReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_sales_summary_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function receivingReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_receiving_reports') || $this->permissionCheck()->hasPermission('can_see_personal_receiving_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function receivingSummaryReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_receiving_summary_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function registerReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_register_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function inventoryReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_inventory_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function paymentReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_payment_reports') || $this->permissionCheck()->hasPermission('can_see_sales_payment_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function paymentSummaryReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_payment_summary_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function productManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_products') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_products')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function productCategoryManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_categories') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_categories')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function productBrandManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_brands') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_brands')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function productGroupManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_groups') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_groups')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function productVariantManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_variant_attribute') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_variant_attribute')) {
                return 'read_only';

            } else {
                return 0;

            }
        }

    }


    public function productUnitManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_units') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            if ($this->permissionCheck()->hasPermission('can_see_units')) {
                return 'read_only';

            } else {
                return 0;

            }
        }
    }

    public function contactsManagePermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_contacts') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function yearlySalesChartReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_sales_statistics_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function availableStockReportPermission()
    {

        if ($this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function availableTaxReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_tax_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function profitLossReportPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_profit_loss_reports') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }

    public function productSettingsPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_product_setting') || $this->permissionCheck()->isAdmin()) {

            return 1;

        } else {

            return 0;
        }
    }

    public function keyboardShortcutPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_keyboard_shortcuts') || $this->permissionCheck()->isAdmin()) {

            return 1;

        } else {

            return 0;
        }
    }

    public function updateSettingPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_manage_updates_setting') || $this->permissionCheck()->isAdmin()) {

            return 1;

        } else {

            return 0;
        }
    }

    public function checkSalesPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_sales_reports') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            return 'personal';
        }
    }

    public function checkReceivingPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_receiving_reports') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            return 'personal';
        }
    }

    public function checkProfitPermission()
    {
        if ($this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            return 'personal';
        }
    }

    public function checkPaymentPermission()
    {
        if ($this->permissionCheck()->hasPermission('can_see_payment_reports') || $this->permissionCheck()->isAdmin()) {
            return 'manage';

        } else {
            return 'personal';
        }
    }

    public function closeOthersCashRegisters()
    {

        if ($this->permissionCheck()->hasPermission('can_close_others_cash_register') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }
    public function customerDetailsPermission()
    {

        if ($this->permissionCheck()->hasPermission('can_see_customer_details') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }

    public function supplierDetailsPermission()
    {

        if ($this->permissionCheck()->hasPermission('can_see_supplier_details') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }

    public function userDetailsPermission()
    {

        if ($this->permissionCheck()->hasPermission('can_see_users_details') || $this->permissionCheck()->isAdmin()) {
            return 1;

        } else {
            return 0;
        }
    }
}
