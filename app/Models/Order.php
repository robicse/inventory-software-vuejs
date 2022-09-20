<?php

namespace App\Models;

use DB;
use App\Http\Controllers\API\PermissionController;
use Carbon\Carbon;

class Order extends BaseModel
{
    protected $fillable = ['date', 'order_type', 'sub_total', 'total_tax', 'all_discount', 'total', 'due_amount','type', 'profit', 'status', 'branch_id', 'transfer_branch_id', 'created_by', 'customer_id', 'supplier_id', 'invoice_id', 'created_at'];

    public static function supplierQuery($id)
    {
        return OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->leftJoin('branches', 'branches.id', '=', 'orders.branch_id')
            ->select('orders.id', 'orders.date', 'branches.name as received_branch', 'orders.sub_total', 'orders.total_tax as tax', 'orders.due_amount', 'orders.total', 'users.id as user_id',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS sold_by"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS received_by"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))+ 
                (select abs(order_items.sub_total) from order_items where order_items.type ='discount' and order_items.order_id = orders.id)) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_received'))
            ->where('orders.supplier_id', '=', $id)
            ->groupBy('order_items.order_id');
    }

    public static function supplierRecords($columnName, $columnSortedBy, $limit, $offset, $id, $searchValue, $requestType)
    {
        $supplierRecords = Order::supplierQuery($id);

        if ($searchValue) {
            $supplierRecords = $supplierRecords->where('orders.id', '=', $searchValue);
        }

        $count = $supplierRecords->get()->count();

        if (empty($requestType)) {
            $allData = $supplierRecords->get();
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->get();

            return ['data' => $data, 'count' => $count];
        }

    }

    public static function supplierRecordsByDate($columnName, $limit, $offset, $searchValue, $id, $columnSortedBy, $filtersData, $requestType)
    {
        $supplierRecords = Order::supplierQuery($id);

        if ($searchValue) {
            $query = $supplierRecords->where('orders.id', '=', $searchValue);
        }

        foreach ($filtersData as $singleFilter) {

            if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                $starts = $singleFilter['value'][0]['start'];
                $ends = $singleFilter['value'][0]['end'];
                $query = $supplierRecords->whereBetween('orders.date', [$starts, $ends]);
            }
        }

        $count = $supplierRecords->get()->count();

        if (empty($requestType)) {
            $allData = $supplierRecords->get();
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->get();

            return ['data' => $data, 'count' => $count];
        }

    }

    public static function customerDetails($id, $filtersData, $searchValue, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $query = OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->select('orders.id', 'orders.date', 'orders.sub_total', 'orders.total_tax as tax', 'orders.total', 'orders.due_amount', 'users.id as user_id',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS sold_by"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))
                + abs(SUM(CASE WHEN order_items.type = 'discount' THEN order_items.sub_total ELSE 0 END)) ) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_purchased'))
            ->where('orders.customer_id', '=', $id)
            ->groupBy('order_items.order_id');

        foreach ($filtersData as $singleFilter) {

            if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                $starts = $singleFilter['value'][0]['start'];
                $ends = $singleFilter['value'][0]['end'];
                $query->whereBetween('orders.date', [$starts, $ends]);
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.id', 'LIKE', '%' . $searchValue . '%');
            });
        }

        $count = $query->get()->count();

        if (empty($requestType)) {
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            $data = $query->orderBy($columnName, $columnSortedBy)->get();
            return ['data' => $data, 'count' => $count];
        }

    }

    public static function orderDetails($id)
    {
        return DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('branches', 'branches.id', '=', 'orders.branch_id')
            ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
            ->where('orders.id', '=', $id)
            ->select('orders.id', 'orders.date', 'orders.invoice_id', 'orders.customer_id', 'payments.paid', 'payments.exchange as change', 'payment_types.name as method', 'users.first_name', 'users.last_name', 'cash_registers.title as cash_register_name', 'branches.name as branch_name')
            ->first();
    }

    public static function holdOrder()
    {
        return Order::select('created_by as createdBy', 'customer_id as customer', 'created_at as date', 'id as orderID', 'type as salesOrReceivingType')->where('status', 'hold')->get();
    }

    public static function userSales($id, $Month, $date)
    {
        return Order::join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->select('orders.date', DB::raw('abs(sum(order_items.price*order_items.quantity)) as sales'))
            ->where('orders.created_by', '=', $id)
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')
            ->whereBetween('orders.date', [$Month, $date])->get();
    }


    public static function salesInvoice($date)
    {
        return Order::select('invoice_id')->where('order_type', '=', 'sales')->where('date', '=', $date)->count();
    }

    public static function todayTotalTax($date)
    {
        return Order::where('order_type', '=', 'sales')->where('date', '=', $date)->sum('total_tax');
    }

    public static function getsOrders($id)
    {
        return Order::select('orders.id', 'orders.sub_total', 'orders.total_tax', 'orders.total')
            ->where('id', $id)
            ->first();
    }

    public static function getsOrdersForUpdate($id)
    {
        return Order::select('orders.id', 'orders.sub_total', 'orders.total_tax', 'orders.total')
            ->where('id', $id)
            ->first();
    }

    public static function taxReports($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = Order::join('branches', 'orders.branch_id', '=', 'branches.id')
            ->select('orders.id', 'orders.date', 'orders.order_type', 'orders.total_tax as tax', 'branches.name', 'orders.total', 'orders.invoice_id as invoice_id');

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {

                    $query->where('orders.order_type', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {

                    $query->where('branches.id', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('orders.id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('branches.name', 'LIKE', '%' . $searchValue . '%');
            });
        }

        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function getSevenDaysProfit()
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $date = carbon::today()->toDateString();
        $prev = carbon::today()->subDays(7)->toDateString();

        $sevenDayProfit = Order::select('orders.date', DB::raw('sum(orders.profit) as profit'))
            ->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.order_type', 'sales')
            ->where('orders.status', 'done')
            ->whereBetween('orders.date', [$prev, $date])
            ->groupBy('orders.date');

        if ($profitPermission != 'manage') {
            return $sevenDayProfit->where('users.id', $userId)->get();
        }
        return $sevenDayProfit->get();
    }

    public static function todaysSale($today)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $todaySales = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '=', $today);


        if ($salesPermission != 'manage') {

            return $todaySales->where('users.id', $userId)->sum('orders.total');
        }

        return $todaySales->sum('orders.total');
    }

    public static function monthlySold($date)
    {
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();
        $userId = Auth()->user()->id;
        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '>=', $date);

        if ($salesPermission != 'manage') {
            return $monthlySale->where('users.id', $userId)->sum('orders.total');
        }

        return $monthlySale->sum('orders.total');


    }

    public static function totalSold()
    {

        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();
        $userId = Auth()->user()->id;
        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales');

        if ($salesPermission != 'manage') {
            return $monthlySale->where('users.id', $userId)->sum('orders.total');
        }

        return $monthlySale->sum('orders.total');

    }

    public static function todayProfit($today)
    {

        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '=', $today);


        if ($profitPermission != 'manage') {

            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }

        return $todayProfit->sum('orders.profit');
    }

    public static function monthlyProfit($date)
    {

        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '>=', $date);

        if ($profitPermission != 'manage') {

            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }

        return $todayProfit->sum('orders.profit');
    }

    public static function totalProfit()
    {

        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales');


        if ($profitPermission != 'manage') {

            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }

        return $todayProfit->sum('orders.profit');
    }

    public static function searchOrders($id)
    {
        return Order::where(function ($query) use ($id) {
            $query->where('id', 'LIKE', '%' . $id . '%')
                ->orWhere('invoice_id', 'LIKE', '%' . $id . '%');
        })->where('order_type', 'sales')->where('status', 'done')->select('created_by as createdBy', 'invoice_id', 'all_discount', 'customer_id as customer', 'created_at as date', 'id as orderID', 'type as salesOrReceivingType')->get();
    }

    public static function getProfit($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkProfitPermission();

        $query = Order::leftJoin('branches', 'branches.id', 'orders.branch_id')
            ->select('orders.id as sales_id', 'orders.date as sales_date', 'branches.name as branch_name', 'orders.total_tax as item_tax', 'orders.total as grand_total', 'orders.profit as profit_amount', 'orders.invoice_id as invoice_id')
            ->where('orders.status', 'done')
            ->where('orders.order_type', 'sales');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $query->where('orders.branch_id', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('branches.name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.id', 'LIKE', '%' . $searchValue . '%');
            });
        }

        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function getInvoiceData($id)
    {
        return Order::join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->join('branches', 'branches.id', '=', 'orders.branch_id')
            ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
            ->where('orders.id', '=', $id)
            ->select('orders.id', 'orders.date', 'orders.invoice_id', 'orders.sub_total', 'orders.total', 'payments.paid', 'payments.exchange as change', 'payment_types.name as method', 'cash_registers.title as cash_register_name', 'branches.name as branch_name', 'customers.first_name', 'customers.last_name',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by_name"),
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name"))
            ->first();
    }

    public static function getHoldOrders()
    {
        return Order::leftJoin('branches', 'branches.id', 'orders.transfer_branch_id')
            ->select('orders.created_by as createdBy', 'orders.all_discount', 'orders.customer_id as customer', 'orders.created_at as date', 'branches.id as transfer_branch_id', 'branches.name as transfer_branch_name', 'orders.id as orderID', 'orders.type as salesOrReceivingType')
            ->where('status', 'hold')
            ->get();
    }

    public static function getOrderDetailsForInvoice($orderId, $orderType)
    {
        if ($orderType == 'sales') {
            return Order::join('payments', 'payments.order_id', '=', 'orders.id')
                ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
                ->join('users', 'users.id', '=', 'orders.created_by')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->join('branches', 'branches.id', '=', 'orders.branch_id')
                ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                ->where('orders.id', '=', $orderId)
                ->select('orders.id', 'orders.date', 'orders.total_tax', 'orders.invoice_id', 'orders.sub_total', 'orders.total', 'orders.due_amount',
                    'payments.paid', 'payments.exchange', 'payment_types.name as method', 'orders.created_at',
                    'cash_registers.title as cash_register_name', 'branches.name as branch_name', 'customers.first_name', 'customers.last_name',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                    DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                )
                ->first();
        } else {
            return Order::join('payments', 'payments.order_id', '=', 'orders.id')
                ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
                ->join('users', 'users.id', '=', 'orders.created_by')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'orders.supplier_id')
                ->join('branches', 'branches.id', '=', 'orders.branch_id')
                ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                ->where('orders.id', '=', $orderId)
                ->select('orders.id', 'orders.date', 'orders.invoice_id', 'orders.sub_total', 'orders.total', 'orders.due_amount', 'orders.created_at',
                    'payments.paid', 'payments.exchange', 'payment_types.name as method',
                    'cash_registers.title as cash_register_name', 'branches.name as branch_name', 'suppliers.first_name', 'suppliers.last_name',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                    DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS supplier_name"))
                ->first();
        }

    }
}
