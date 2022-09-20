<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\ProductAttributeValue;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Tax;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\CustomUser;
use Illuminate\Support\Facades\Lang;

class ReportController extends Controller
{

    public function index()
    {
        $tabName = '';
        $routeName = '';
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }

        return view('reports.ReportsIndex', ['tab_name' => $tabName, 'route_name' => $routeName]);
    }

    private function compare($a, $b)
    {
        return strcmp($a->sub_total, $b->sub_total);
    }

    public function salesReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $sales = OrderItems::salesItems($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        //die(var_dump($sales->all()));

        if (empty($requestType)) {
            $salesData = $sales['data'];

        } else {
            $salesData = $sales;
        }

        if (empty($requestType)) {
            $salesItems = $this->calculateSales($salesData);

            $arrayCount = $salesItems['count'];
            $totalCount = count($sales['allData']);
            $salesData[$arrayCount] = [
                'invoice_id' => Lang::get('lang.total'),
                'item_purchased' => $salesItems['netItem'],
                'tax' => $salesItems['netTax'],
                'discount' => $salesItems['discount'],
                'total' => $salesItems['netTotal'],
                'due_amount' => $salesItems['netDueAmount']
            ];

            $grandCalculation = $this->calculateSales($sales['allData']);

            $salesData[$arrayCount + 1] = [
                'invoice_id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandCalculation['netItem'],
                'tax' => $grandCalculation['netTax'],
                'discount' => $grandCalculation['discount'],
                'total' => $grandCalculation['netTotal'],
                'due_amount' => $salesItems['netDueAmount']
            ];

            return ['datarows' => $salesData, 'count' => $totalCount];

        } else {
            $this->calculateSales($salesData);

            return ['datarows' => $salesData];
        }

    }


    public function calculateSales($salesData)
    {
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;
        $netDueAmount = 0;

        foreach ($salesData as $rowData) {
            if ($rowData->type == 'customer') {
                $rowData->type = Lang::get('lang.customer');
            } else {
                $rowData->type = Lang::get('lang.internal_sales');
                $rowData->customer = $rowData->transfer_branch_name;
            }
            if ($rowData->customer == '') $rowData->customer = Lang::get('lang.walk_in_customer');
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $netDueAmount += $rowData->due_amount;
            $arrayCount++;
        }

        return [
            'netTotal' => $netTotal,
            'netTax' => $netTax,
            'netItem' => $netItem,
            'discount' => $netDiscount,
            'count' => $arrayCount,
            'netDueAmount' => $netDueAmount
        ];
    }

    public function salesSummaryReport(Request $request)
    {
        $filterKey = 'product_brands.name as filter_key';
        $groupBy = 'products.brand_id';
        $joinTable = 'product_brands';
        $joinColumn1 = 'product_brands.id';
        $joinColumn2 = 'products.brand_id';
        $branchId = 0;
        $dateFormat = false;
        $requestType = $request->reqType;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $columnName = 'product_brands.name';
        $columnSortedBy = $request->columnSortedBy;

        if (empty($filtersData)) {
            $summary = OrderItems::salesSummary($filterKey, $limit, $request->rowOffset, $groupBy, $requestType, $columnName, $columnSortedBy);
        } else {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $filter = $singleFilter['value'];
                    if ($filter == 'brand') {
                        $filterKey = 'product_brands.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_brands';
                        $joinColumn1 = 'product_brands.id';
                        $joinColumn2 = 'products.brand_id';
                        $columnName = 'product_brands.name';

                    } else if ($filter == 'category') {
                        $filterKey = 'product_categories.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_categories';
                        $joinColumn1 = 'product_categories.id';
                        $joinColumn2 = 'products.category_id';
                        $columnName = 'product_categories.name';

                    } else if ($filter == 'group') {
                        $filterKey = 'product_groups.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_groups';
                        $joinColumn1 = 'product_groups.id';
                        $joinColumn2 = 'products.group_id';
                        $columnName = 'product_groups.name';

                    } else if ($filter == 'customer') {
                        $groupBy = 'customers.first_name';
                        $filterKey = DB::raw('concat(customers.first_name," ",customers.last_name) as filter_key');
                        $joinTable = 'customers';
                        $joinColumn1 = 'customers.id';
                        $joinColumn2 = 'orders.customer_id';
                        $columnName = 'customers.first_name';

                    } else if ($filter == 'employee') {
                        $filterKey = DB::raw('concat(users.first_name," ",users.last_name) as filter_key');
                        $groupBy = 'orders.created_by';
                        $joinTable = 'users';
                        $joinColumn1 = 'users.id';
                        $joinColumn2 = 'orders.created_by';
                        $columnName = 'users.first_name';

                    } else if ($filter == 'product') {
                        $filterKey = DB::raw('concat(title,if(variant_title="default_variant","",concat("(",product_variants.variant_title,")"))) as filter_key');
                        $groupBy = 'order_items.variant_id';
                        $joinTable = 'product_variants';
                        $joinColumn1 = 'product_variants.id';
                        $joinColumn2 = 'order_items.variant_id';
                        $columnName = 'products.title';

                    } else if ($filter == 'date') {
                        $filterKey = 'orders.date as filter_key';
                        $groupBy = 'orders.date';
                        $dateFormat = true;
                        $columnName = 'orders.date';
                    }

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $branchId = $singleFilter['value'];
                }
            }

            $starts = 0;
            $ends = 0;
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                }
            }

            $summary = OrderItems::salesSummaryTypeFilter($filterKey, $limit, $request->rowOffset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy);
        }

        if (empty($requestType)) {
            $summaryData = $summary['data'];

        } else {
            $summaryData = $summary;
        }

        foreach ($summaryData as $rowData) {
            if ($rowData->filter_key == '') $rowData->filter_key = Lang::get('lang.walk_in_customer');
            if ($dateFormat) {
                $allSettingFormat = new AllSettingFormat;
                $rowData->filter_key = $allSettingFormat->getDate($rowData->filter_key);
            }
        }
        if (empty($requestType)) {
            $totalCount = $summary['count'];
            $salesSummary = $this->calculateSalesSummary($summaryData);
            $arrayCount = $salesSummary['count'];
            $summaryData[$arrayCount] = ['filter_key' => Lang::get('lang.total'), 'item_purchased' => $salesSummary['netItem'], 'discount' => $salesSummary['discount'], 'sub_total' => $salesSummary['netSubTotal'], 'tax' => $salesSummary['netTax'], 'total' => $salesSummary['netTotal']];
            $grandCalculation = $this->calculateSalesSummary($summary['allData']);
            $summaryData[$arrayCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'item_purchased' => $grandCalculation['netItem'], 'discount' => $salesSummary['discount'], 'sub_total' => $grandCalculation['netSubTotal'], 'tax' => $grandCalculation['netTax'], 'total' => $grandCalculation['netTotal']];

            return ['datarows' => $summaryData, 'count' => $totalCount];

        } else {

            $this->calculateSalesSummary($summaryData);
            return ['datarows' => $summaryData];
        }
    }

    public function calculateSalesSummary($salesSummary)
    {

        $netSubTotal = 0;
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;

        foreach ($salesSummary as $rowData) {

            $rowData->total = $rowData->sub_total + $rowData->tax - $rowData->discount;
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $arrayCount++;
            $netSubTotal += $rowData->sub_total;
        }

        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'netItem' => $netItem, 'discount' => $netDiscount, 'netSubTotal' => $netSubTotal, 'count' => $arrayCount];
    }

    public function salesReportsDetails($id)
    {
        $tabName = "";
        $routeName = "";
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view('reports.SalesReportsDetails',
            [
                'id' => $id,
                'tabName' => $tabName,
                'route_name' => $routeName
            ]);

    }

    public function getSalesDetails(Request $request, $id)
    {
        $count = 0;
        $details = OrderItems::getOrderDetails($id);
        
        foreach ($details as $item) {
            if ($item->title == 'Discount') {
                $item->price = null;
                $item->quantity = null;
                $item->discount = null;
            } else {
                $item->discount = $this->formateDiscount($item->discount);
                $item->price = $this->formateDiscount($item->price);
            }
            $count++;
        }

        $orders = Order::getsOrders($id);
        
        $details[$count++] = ['title' => Lang::get('lang.sub_total'), 'total' => $orders->sub_total];
        $details[$count++] = ['title' => Lang::get('lang.vat'), 'total' => $orders->total_tax];
        $details[$count++] = ['title' => Lang::get('lang.total'), 'total' => $orders->total];

        $payments = Payments::paymentDetails($id);
        
        foreach ($payments as $payment) {
            $details[$count++] = ['title' => $payment->name, 'total' => $payment->paid];
        }
        return ['datarows' => $details, 'count' => 0];
    }

    public function getOrderDataDetails(Request $request, $id)
    {
        /*die($id);*/ // 11 order table er id
        $count = 0;
        $details = OrderItems::getOrderDetailsForUpdate($id);
        //die($details);
        // output
        /*[
            {
                "price":400,
                "type":"sales",
                "total":380,
                "title":"test product 3 ",
                "quantity":1,
                "discount":5
            },
            {
                "price":800,
                "type":"sales",
                "total":760,
                "title":"test product 4 ",
                "quantity":1,"discount":5
            }
        ]*/


        $orders = Order::getsOrdersForUpdate($id);
        //die($orders); // {"id":11,"sub_total":"1140","total_tax":"0","total":1140}

        
        return ['datarows' => $details, 'count' => 0, 'orders' => $orders];
    }

    public function getStockInProductDetails(Request $request, $id)
    {
        /*die($id);*/ // 11 order table er id
        $count = 0;
        $details = OrderItems::getStockInProductDetailsForUpdate($id);
        //die($details);

        //$orders = Order::getsLastStockInOrdersForUpdate($id);
        //die($orders); // {"id":11,"sub_total":"1140","total_tax":"0","total":1140}

        
        //return ['datarows' => $details, 'count' => 0, 'orders' => $orders];
        return ['datarows' => $details, 'count' => 0];
    }

    public function formateDiscount($discount)
    {
        $allSettingFormat = new AllSettingFormat;
        return $allSettingFormat->getCurrencySeparator($discount);
    }

    public function calculateOrderDetails($orderDetails)
    {
        $totalQuantity = 0;
        $subTotal = 0;
        $totalTax = 0;
        $allTotal = 0;
        $count = 0;
        foreach ($orderDetails as $item) {

            if ($item->tax != null) {
                $taxAmount = $item->calculatingTax;
            } else {
                $taxAmount = 0;
            }
            $tax = ($item->subtotal * $taxAmount) / 100;

            if ($item->discount_type == "%") {
                $discount = ($item->subTotal * $item->discount) / 100;
            } else {
                $discount = $item->discount;
            }
            $item->total = ($item->subtotal - $discount) + $taxAmount;
            $count++;
            $totalQuantity = $totalQuantity + $item->quantity;
            $subTotal = $subTotal + $item->subtotal;
            $totalTax = $totalTax + $taxAmount;
            $allTotal = $allTotal + $item->total;
        }
        return ['totalQuantity' => $totalQuantity, 'subTotal' => $subTotal, 'totalTax' => $totalTax, 'allTotal' => $allTotal, 'count' => $count];
    }

    public function receivingReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;

        $receiving = OrderItems::receivingItems($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        if (empty($requestType)) {
            $receivingItems = $receiving['data'];
            $totalCalculation = $this->calculateReceivings($receiving['data']);
            $arrayCount = $totalCalculation['count'];
            $totalCount = count($receiving['allData']);
            $receivingItems[$arrayCount] = ['invoice_id' => Lang::get('lang.total'), 'item_purchased' => $totalCalculation['netItem'], 'total' => $totalCalculation['total'], 'due_amount' => $totalCalculation['netDue']];

            $grandCalculation = $this->calculateReceivings($receiving['allData']);

            $receivingItems[$arrayCount + 1] = ['invoice_id' => Lang::get('lang.grand_total'), 'item_purchased' => $grandCalculation['netItem'], 'total' => $grandCalculation['total'], 'due_amount' => $totalCalculation['netDue']];

            return ['datarows' => $receivingItems, 'count' => $totalCount];

        } else {

            $this->calculateReceivings($receiving);
            return ['datarows' => $receiving];
        }

    }

    public function calculateReceivings($receivingData)
    {
        $netTotal = 0;
        $netItem = 0;
        $netDue = 0;
        $arrayCount = 0;

        foreach ($receivingData as $rowData) {

            if ($rowData->type == 'supplier') {
                $rowData->type = Lang::get('lang.supplier');

            } else {
                $rowData->type = Lang::get('lang.internal_receivings');
            }

            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDue += $rowData->due_amount;
            $arrayCount++;
        }

        return ['total' => $netTotal, 'netItem' => $netItem, 'netDue' => $netDue, 'count' => $arrayCount];
    }

    public function receivingSummary(Request $request)
    {
        $filterKey = 'product_brands.name as filter_key';
        $groupBy = 'products.brand_id';
        $joinTable = 'product_brands';
        $joinColumn1 = 'product_brands.id';
        $joinColumn2 = 'products.brand_id';
        $branchId = 0;
        $dateFormat = false;
        $requestType = $request->reqType;

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $columnName = 'product_brands.name';
        $columnSortedBy = $request->columnSortedBy;

        if (empty($filtersData)) {
            $summaryData = OrderItems::receiveSummary($filterKey, $limit, $request->rowOffset, $groupBy, $requestType, $columnName, $columnSortedBy);
        } else {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $filter = $singleFilter['value'];

                    if ($filter == 'brand') {
                        $filterKey = 'product_brands.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_brands';
                        $joinColumn1 = 'product_brands.id';
                        $joinColumn2 = 'products.brand_id';
                        $columnName = 'product_brands.name';
                    }

                    if ($filter == 'category') {
                        $filterKey = 'product_categories.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_categories';
                        $joinColumn1 = 'product_categories.id';
                        $joinColumn2 = 'products.category_id';
                        $columnName = 'product_categories.name';
                    }

                    if ($filter == 'group') {
                        $filterKey = 'product_groups.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_groups';
                        $joinColumn1 = 'product_groups.id';
                        $joinColumn2 = 'products.group_id';
                        $columnName = 'product_groups.name';
                    }

                    if ($filter == 'supplier') {
                        $filterKey = DB::raw('concat(suppliers.first_name," ",suppliers.last_name) as filter_key');
                        $groupBy = 'suppliers.first_name';
                        $joinTable = 'suppliers';
                        $joinColumn1 = 'suppliers.id';
                        $joinColumn2 = 'orders.supplier_id';
                        $columnName = 'suppliers.first_name';
                    }

                    if ($filter == 'employee') {
                        $filterKey = DB::raw('concat(users.first_name," ",users.last_name) as filter_key');
                        $groupBy = 'orders.created_by';
                        $joinTable = 'users';
                        $joinColumn1 = 'users.id';
                        $joinColumn2 = 'orders.created_by';
                        $columnName = 'users.first_name';
                    }

                    if ($filter == 'product') {
                        $filterKey = DB::raw('concat(title,if(variant_title="default_variant","",concat("(",product_variants.variant_title,")"))) as filter_key');
                        $groupBy = 'order_items.variant_id';
                        $joinTable = 'product_variants';
                        $joinColumn1 = 'product_variants.id';
                        $joinColumn2 = 'order_items.variant_id';
                        $columnName = 'products.title';
                    }

                    if ($filter == 'date') {
                        $filterKey = 'orders.date as filter_key';
                        $groupBy = 'orders.date';
                        $dateFormat = true;
                        $columnName = 'orders.date';
                    }

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $branchId = $singleFilter['value'];
                }
            }

            $starts = 0;
            $ends = 0;

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                }
            }

            $summaryData = OrderItems::receiveSummaryFilter($filterKey, $limit, $request->rowOffset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy);
        }

        if (empty($requestType)) {
            $receiveSummary = $summaryData['data'];

        } else {
            $receiveSummary = $summaryData;
        }

        foreach ($receiveSummary as $rowQuery) {
            if ($rowQuery->filter_key == '') $rowQuery->filter_key = Lang::get('lang.walk_in_supplier');

            if ($dateFormat) {
                $allSettingFormat = new AllSettingFormat;
                $rowQuery->filter_key = $allSettingFormat->getDate($rowQuery->filter_key);
            }
        }

        if (empty($requestType)) {

            $totalCalculation = $this->calculateReceivingSummary($receiveSummary);
            $arrayCount = $totalCalculation['count'];
            $totalCount = $summaryData['count'];
            $receiveSummary[$arrayCount] = ['filter_key' => Lang::get('lang.total'), 'item_receive' => $totalCalculation['netItem'], 'total' => $totalCalculation['total']];
            $grandCalculation = $this->calculateReceivingSummary($summaryData['allData']);
            $receiveSummary[$arrayCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'item_receive' => $grandCalculation['netItem'], 'total' => $grandCalculation['total']];

            return ['datarows' => $receiveSummary, 'count' => $totalCount];

        } else {
            $this->calculateReceivingSummary($receiveSummary);
            return ['datarows' => $receiveSummary];
        }
    }

    public function calculateReceivingSummary($receivingData)
    {

        $netTotal = 0;
        $netItem = 0;
        $arrayCount = 0;

        foreach ($receivingData as $rowData) {
            $rowData->total = $rowData->sub_total + $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_receive;
            $arrayCount++;
        }

        return ['total' => $netTotal, 'netItem' => $netItem, 'count' => $arrayCount];
    }

    public function receivingReportsDetails($id)
    {
        $tabName = "";
        $routeName = "";
        if (isset($_GET['tab_name'])) {
            $tabName = $_GET['tab_name'];
        }
        if (isset($_GET['route_name'])) {
            $routeName = $_GET['route_name'];
        }
        return view('reports.receiveDetailsReports',
            [
                'id' => $id,
                'tabName' => $tabName,
                'route_name' => $routeName
            ]);
    }

    public function registerLogReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;
        $registerLogData = CashRegisterLog::registerLogFilter($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        if (empty($requestType)) {

            $registerLogs = $registerLogData['data'];

        } else {
            $registerLogs = $registerLogData;
        }

        foreach ($registerLogs as $registerLog) {

            $paymentInfo = Payments::getPaymentInfo($registerLog->cash_register_id, $registerLog->opening_time, $registerLog->closing_time);

            $registerLog->cash_receives = $paymentInfo['receiving'];
            $registerLog->cash_sales = $paymentInfo['sales'];


            if ($registerLog->difference == null) {
                $registerLog->difference = '';
            }

            if ($registerLog->closing_amount == null) {
                $registerLog->closing_amount = '';
            }

            if ($registerLog->closed_by) {
                $registerLog->difference = (float)$registerLog->opening_amount + (float)$registerLog->cash_sales - (float)$registerLog->cash_receives - (float)$registerLog->closing_amount;
                $registerLog->closed_user = CustomUser::userInfo($registerLog->closed_by);
            }

            if ($registerLog->status == 'closed') {
                $registerLog->status = Lang::get('lang.closed');

            } else {
                $registerLog->status = Lang::get('lang.open');
                $registerLog->closing_amount = '';
            }
        }

        if (empty($requestType)) {
            $totalCount = $registerLogData['count'];

            return ['datarows' => $registerLogs, 'count' => $totalCount];

        } else {
            return ['datarows' => $registerLogData];
        }
    }

    public function getCashRegisterFilterData()
    {

        $cashRegisters = CashRegister::index(['title as text', 'id as value']);
        $user = CustomUser::getAll([DB::raw('concat(first_name," ",last_name) as text'), 'id as value'], 'user_type', 'staff');
        $branch = Branch::index(['name as text', 'id as value']);

        return ['user' => $user, 'branch' => $branch, 'cashRegisters' => $cashRegisters];

    }

    public function inventoryReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $offset = $request->rowOffset;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;
        $inventory = ProductVariant::inventoryReports($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $offset, $requestType);
        $innventories = $inventory['data'];
        $totalCount = $inventory['count'];
        //die(var_dump($totalCount));
        foreach ($innventories as $item) {

            if ($item->variantTitle == 'default_variant') {
                $item->variantTitle = '';
            }
        }
        if (empty($requestType)) {
            return ['datarows' => $innventories, 'count' => $totalCount];

        } else {
            return ['datarows' => $innventories];
        }

    }

    public function inventoryReportsFilter()
    {
        $branchName = Branch::index(['name as text', 'id as value']);
        $brandName = ProductBrand::index(['name as text', 'id as value']);
        $categoryName = ProductCategory::index(['name as text', 'id as value']);
        $groupName = ProductGroup::index(['name as text', 'id as value']);

        return ['branchName' => $branchName, 'brandName' => $brandName, 'categoryName' => $categoryName, 'groupName' => $groupName];
    }

    public function paymentReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $payments = Payments::paymentReportList($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        foreach ($payments['data'] as $payment) {

            if ($payment->paid_by == null) {
                $payment->paid_by = Lang::get('lang.walk_in_customer');
            }

            if ($payment->order_type == 'sales') {
                $payment->route = 'customer';

            } else {
                $payment->route = 'user';
            }
        }

        if (empty($requestType)) {
            $totalCount = $payments['count'];
            $paymentData = $payments['data'];
            $totalCalculation = $this->calculatePayment($paymentData);
            $rowCount = $totalCalculation['count'];

            $paymentData[$rowCount] = ["invoice_id" => Lang::get('lang.total'), "paid" => $totalCalculation['totalPaid'], 'change' => $totalCalculation['totalChange']];

            $grandCalculation = $this->calculatePayment($payments['allData']);

            $paymentData[$rowCount + 1] = ["invoice_id" => Lang::get('lang.grand_total'), "paid" => $grandCalculation['totalPaid'], 'change' => $grandCalculation['totalChange']];

            return ['datarows' => $paymentData, 'count' => $totalCount];

        } else {
            return ['datarows' => $payments['data']];
        }
    }

    public function calculatePayment($paymentData)
    {
        $totalPaid = 0;
        $totalChange = 0;
        $rowCount = 0;

        foreach ($paymentData as $rowPayment) {
            $totalPaid += $rowPayment->paid;
            $totalChange += $rowPayment->change;
            $rowCount++;
        }

        return ['totalPaid' => $totalPaid, 'totalChange' => $totalChange, 'count' => $rowCount];
    }

    public function paymentSummary(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $total = 0;
        $requestType = $request->reqType;

        $paymentSummary = Payments::paymentSummary($filtersData, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        if (empty($requestType)) {
            $totalCount = $paymentSummary['count'];
            $payment = $paymentSummary['data'];
            $dateFormat = false;

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "date") {
                    $dateFormat = true;
                }
            }

            if (empty($filtersData) || $dateFormat) {

                foreach ($payment as $SinglePayment) {
                    $allSettingFormat = new AllSettingFormat;
                    $SinglePayment->filter_key = $allSettingFormat->getDate($SinglePayment->filter_key);
                }
            } else {
                foreach ($payment as $SinglePayment) {
                    if ($SinglePayment->filter_key == null) $SinglePayment->filter_key = Lang::get('lang.walk_in_customer');
                }

            }
            $calculateTotal = $this->calculatePaymentSummary($payment);
            $rowCount = $calculateTotal['count'];
            $payment[$rowCount] = ['filter_key' => Lang::get('lang.total'), 'total' => $calculateTotal['total']];

            $calculateGrandTotal = $this->calculatePaymentSummary($paymentSummary['allData']);

            $payment[$rowCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'total' => $calculateGrandTotal['total']];

            return ['datarows' => $payment, 'count' => $totalCount];

        } else {
            $this->calculatePaymentSummary($paymentSummary);
            return ['datarows' => $paymentSummary];
        }
    }

    public function calculatePaymentSummary($paymentSummary)
    {
        $total = 0;
        $count = 0;

        foreach ($paymentSummary as $value) {

            $typeArray = explode(',', $value->type);
            for ($i = 0; $i < count($typeArray); $i++) {

                if ($typeArray[$i] == 'sales') {
                    $typeArray[$i] = Lang::get('lang.sales');

                } else {
                    $typeArray[$i] = Lang::get('lang.receiving');
                }
            }

            $value->type = implode(',', $typeArray);

            $total = $total + $value->total;
            $count++;
        }

        return ['total' => $total, 'count' => $count];
    }

    public function paymentReportFilter()
    {
        $cashRegister = CashRegister::index(['title as text', 'id as value']);
        $paymentMethod = PaymentType::index(['name as text', 'id as value']);

        return ['cashRegister' => $cashRegister, 'paymentMethod' => $paymentMethod];
    }

    public function paymentSummaryReportFilter()
    {
        $paymentMethod = PaymentType::index(['name as text', 'id as value']);

        return ['paymentMethod' => $paymentMethod];
    }

    public function customerPurchaseReport(Request $request, $id)
    {

        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;
        $customerDetails = Order::customerDetails($id, $filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        $customerPurchase = $customerDetails['data'];

        $total = $this->calculateCustomerPurchase($customerPurchase);

        if (empty($requestType)) {
            $count = $total['count'];
            $customerPurchase[$count] = [
                'id' => Lang::get('lang.total'),
                'item_purchased' => $total['item_purchased'],
                'sub_total' => $total['subTotal'],
                'tax' => $total['tax'],
                'discount' => $total['discount'],
                'total' => $total['total'],
                'due_amount'=> $total['due_amount']
            ];

            $grandTotal = $this->calculateCustomerPurchase($customerDetails['allData']);
            $customerPurchase[$count + 1] = [
                'id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandTotal['item_purchased'],
                'sub_total' => $grandTotal['subTotal'],
                'tax' => $grandTotal['tax'],
                'discount' => $grandTotal['discount'],
                'total' => $grandTotal['total'],
                'due_amount'=> $grandTotal['due_amount']
            ];
        }

        return ['datarows' => $customerPurchase, 'count' => $customerDetails['count']];
    }

    public function calculateCustomerPurchase($purchaseRecord)
    {
        $netTotal = 0;
        $netSubTotal = 0;
        $netTax = 0;
        $netDue = 0;
        $discount = 0;
        $count = 0;
        $item = 0;

        foreach ($purchaseRecord as $rowData) {
            $netTax += $rowData->tax;
            $netSubTotal += $rowData->sub_total;
            $netTotal += $rowData->total;
            $discount += $rowData->discount;
            $item += $rowData->item_purchased;
            $netDue += $rowData->due_amount;
            $count++;
        }
        return ['item_purchased' => $item, 'subTotal' => $netSubTotal, 'tax' => $netTax, 'discount' => $discount, 'total' => $netTotal, 'count' => $count, 'due_amount' => $netDue];
    }

    public function getOrdersDetails($id)
    {
        $orderDetails = Order::orderDetails($id);
        $allSettingFormat = new AllSettingFormat;

        $orderDetails->paid = $allSettingFormat->getCurrencySeparator($orderDetails->paid);
        $orderDetails->change = $allSettingFormat->getCurrencySeparator($orderDetails->change);
        $orderDetails->date = $allSettingFormat->getDate($orderDetails->date);

        if ($orderDetails->customer_id == null) {
            $orderDetails->customer_id = '';
        }

        if ($orderDetails->change == null) {
            $orderDetails->change = 0;
        }

        return ['orders_details' => $orderDetails];
    }

    public function yearlySalesChart(Request $request)
    {
        $salesFilterData = $request->filterData;
        $year = date("Y");
        $currentMonth = date("m");

        if (!isset($salesFilterData['duration'])) {
            $salesFilterData['duration'] = 'this_year';
        }

        $duration = $salesFilterData['duration'];

        $salesChart = Payments::yearFilter($salesFilterData, $year, $currentMonth);

        if ($duration == 'last_month' || $duration == 'this_month') {

            if ($duration == 'this_month') {
                $days = date("t");

            } else {
                $days = date("t", mktime(0, 0, 0, date("n") - 1));

            }

            return ['salesChartData' => $salesChart, "salesFilterData" => $salesFilterData, "days" => $days];

        } else {
            $salesChartData = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

            foreach ($salesChart as $data) {
                $salesChartData[$data->month - 1] = $data->sales;
            }
        }

        return ['salesChartData' => $salesChartData, "salesFilterData" => $salesFilterData];
    }

    public function availableStockChart()
    {
        return Product::availableStock();
    }

    public function getBranchAndUser()
    {
        $user = CustomUser::getAll('*', 'user_type', 'staff');
        $branch = Branch::allData();

        return ['user' => $user, 'branch' => $branch];
    }

    public function taxReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $tax = Order::taxReports($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {

            $taxData = $tax['data'];
            $totalCount = $tax['count'];

            $taxItems = $this->calculateTax($taxData);
            $arrayCount = $taxItems['count'];

            $taxData[$arrayCount] = ['invoice_id' => Lang::get('lang.total'), 'total' => $taxItems['netTotal'], 'tax' => $taxItems['netTax']];

            $grandCalculation = $this->calculateTax($tax['allData']);

            $taxData[$arrayCount + 1] = ['invoice_id' => Lang::get('lang.grand_total'), 'total' => $grandCalculation['netTotal'], 'tax' => $grandCalculation['netTax']];

            return ['datarows' => $taxData, 'count' => $totalCount];

        } else {
            $this->calculateTax($tax);
            return ['datarows' => $tax];
        }
    }

    public function calculateTax($data)
    {
        $netTotal = 0;
        $netTax = 0;
        $arrayCount = 0;

        foreach ($data as $rowData) {

            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $arrayCount++;

            if ($rowData->order_type == 'sales') {
                $rowData->order_type = Lang::get('lang.sales');
            } else {
                $rowData->order_type = Lang::get('lang.receiving');
            }
        }

        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'count' => $arrayCount];
    }

    public function profitLossReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $profit = Order::getProfit($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {
            $profitData = $profit['data'];

        } else {
            $profitData = $profit;
        }

        if (empty($requestType)) {
            $totalCount = $profit['count'];

            $totalProfit = $this->calculateProfit($profitData);
            $rowCount = $totalProfit['count'];

            $profitData[$rowCount] = ["invoice_id" => Lang::get('lang.total'), "grand_total" => $totalProfit['netTotal'], 'item_tax' => $totalProfit['netTax'], 'profit_amount' => $totalProfit['netProfit']];

            $grandProfit = $this->calculateProfit($profit['allData']);
            $profitData[$rowCount + 1] = ["invoice_id" => Lang::get('lang.grand_total'), "grand_total" => $grandProfit['netTotal'], 'item_tax' => $grandProfit['netTax'], 'profit_amount' => $grandProfit['netProfit']];

            return ['datarows' => $profitData, 'count' => $totalCount];

        } else {

            return ['datarows' => $profitData];
        }
    }

    public function calculateProfit($data)
    {
        $netTotal = 0;
        $netTax = 0;
        $netProfit = 0;
        $arrayCount = 0;

        foreach ($data as $rowData) {

            $netTax += $rowData->item_tax;
            $netTotal += $rowData->grand_total;
            $netProfit += $rowData->profit_amount;
            $arrayCount++;
        }

        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'netProfit' => $netProfit, 'count' => $arrayCount];
    }

    public function updateOrderDetails(Request $request)
    {
        /*$this->validate($request, [
            'quantity' => 'required',
        ]);*/


        // show network
        //die('come here');
        //return response()->json([$request->all()]);










        /*sales calculation on 10% discount:

        order_items:
        price 5000 tk er 10% discount = 500
        price 600 tk er 10% discount = 60
        total discount = 560

        sub_total = 4500 tk er 5% vat = 225 tk
        sub_total = 540 tk er 5% vat = 27 tk


        orders:
        total sub_total = 5040
        total vat = 252 tk
        total = 5292
        profit = price difference (1000*1 + 100*2) = 1200 => (1200-560)=640*/

        if(count($request->all()) > 0)
        {
            $data = $request->all();
            //return $data[0]['id'];

            $sum_sub_total = 0;
            $order_flag = false; 

            foreach ($request->all() as $key => $value) {
                $id         = $value['id'];
                $quantity   = $value['quantity'];
                $price      = $value['price'];
                $discount   = $value['discount'];
                $type       = $value['type'];
                $order_id   = $value['order_id'];
                
                $t_price = $price*$quantity;
                if($discount)
                {
                    $d_price = $discount*$t_price/100;
                    $sub_total = $t_price - $d_price;
                }else{
                    $sub_total = $t_price;
                }
                
                $sum_sub_total += $sub_total;
                
                if($type == 'sales')
                {
                    $quantity = '-'.$value['quantity'];
                }else{
                    $quantity = $value['quantity'];
                }

                // Order Details Update
                $orderItem = OrderItems:: find($id);
                $orderItem->quantity = $quantity;
                if($type != 'discount')
                {
                    $orderItem->sub_total= $sub_total;
                }
                $orderItem->save();

                $order_flag = true;
            }

            if($order_flag && $order_id)
            {
                // Order Update
                $orderDetails = OrderItems::where('order_id', $order_id)
                    ->where(function ($query) {
                        $query->where('type','receiving')
                        ->orWhere('type','sales');
                    })
                    ->get();
                //die($orderDetails);
                $sum_profit = 0;
                $sum_tax = 0;
                $sum_order_sub_total = 0;
                $sum_order_total = 0;
                $discount_price = 0;
                $sum_o_percentage_discount = 0;
                $discount_price = 0;

                foreach($orderDetails as $orderDetail){
                    //die($orderDetail);
                    /*echo '<pre>';
                    print_r($orderDetail);
                    echo '</pre>';*/
                    
                    $o_product_id = $orderDetail['product_id'];
                    $o_tax_id     = $orderDetail['tax_id'];
                    $o_discount   = $orderDetail['discount'];
                    $o_price      = $orderDetail['price'];
                    $sub_total    = $orderDetail['sub_total'];
                    $o_type       = $orderDetail['type'];

                    // all product discount
                    if($o_discount > 0)
                    {
                        $o_percentage_discount  = $o_price*$o_discount/100;
                        //$o_sub_total  = $o_price - $o_percentage_discount;
                        $sum_o_percentage_discount += $o_percentage_discount;
                    }
                    //echo $o_sub_total;
                    //echo $orderDetail['quantity'];
                    if($o_type == 'sales'){
                        $sellingPrice = ProductVariant::where('product_id',$o_product_id)->select('selling_price','purchase_price')->first();
                        $profit = $sellingPrice['selling_price'] - $sellingPrice['purchase_price'];
                        $sum_profit += ($profit*abs($orderDetail['quantity']))-$o_percentage_discount;
                    }

                    if($o_type == 'receiving' && $o_discount > 0){
                        $sum_profit -= $sum_o_percentage_discount;
                    }


                    // tax amount
                    if($o_tax_id)
                    {
                        $taxs = Tax::where('id',$o_tax_id)->select('percentage')->first();
                        $tax_percentage = $taxs['percentage'];
                        //$tax = ($tax_percentage*$o_price)/100;
                        //$calculate_tax = $tax*abs($o_quantity);
                        $calculate_tax = ($tax_percentage*$sub_total)/100;
                        $sum_tax += $calculate_tax;
                        //$sum_profit -= $sum_tax;
                    }
                    //echo $sum_tax;

                    $sum_order_sub_total += $sub_total;

                }
                //echo $sum_profit;

                // entire sale discount
                $discountPrice = OrderItems::where('order_id', $order_id)->where('type', 'discount')->select('price')->first();
                if($discountPrice){
                    $discount_price = $discountPrice['price'];
                }
                //echo $discount_price;

                //echo $discount_price;
                //echo $sum_tax;
                //echo $sum_order_sub_total;
                $sum_order_total = ($sum_order_sub_total + $sum_tax) - $discount_price;

                $order = Order::find($order_id);
                $order->sub_total = $sum_order_sub_total;
                $order->total_tax = $sum_tax;
                $order->total = $sum_order_total;
                $order->profit = $sum_profit;
                $order->updated_at = date('Y-m-d h:i:s');
                $order->save();

                // Payment Update
                $payment = Payments::where('order_id', $order_id)->first();
                $payment->paid = $sum_order_total;
                $payment->save();
            }


            $response = [
                    'message' => Lang::get('lang.order_details') . ' ' . Lang::get('lang.successfully_updated')
                ];

            return response()->json($response, 201);
            
        }else{
            $response = [
                    'message' => Lang::get('lang.getting_problems')
                ];

            return response()->json($response, 404);
        }
    }
}