@extends('layouts.app')

@section('title', trans("lang.reports"))

@section('content')
    @inject('permission', 'App\Http\Controllers\API\PermissionController')
    <reports-index
            sales_report={{$permission->salesReportPermission()}}
            sales_summary_reports={{$permission->salesSummaryReportPermission()}}
            receiving_report={{$permission->receivingReportPermission()}}
            receiving_summary={{$permission->receivingSummaryReportPermission()}}
            register_report={{$permission->registerReportPermission()}}
            inventory_report={{$permission->inventoryReportPermission()}}
            payment_report={{$permission->paymentReportPermission()}}
            payment_summary_report={{$permission->paymentSummaryReportPermission()}}
            yearly_sales_chart={{$permission->yearlySalesChartReportPermission()}}
            available_stock_chart={{$permission->availableStockReportPermission()}}
            available_tax_report={{$permission->availableTaxReportPermission()}}
            profit_loss_report={{$permission->profitLossReportPermission()}}
            tab_name={{$tab_name}}
            route_name={{$route_name}}
    >

    </reports-index>
@endsection