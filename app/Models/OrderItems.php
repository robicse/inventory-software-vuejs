<?php

namespace App\Models;

use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\PermissionController;

class OrderItems extends BaseModel
{
    protected $fillable = ['product_id', 'variant_id', 'quantity', 'price', 'discount', 'tax_id', 'order_id'];

    public static function monthlySale($year)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->select(DB::raw('sum(orders.total) as sales'), DB::raw('month(orders.date) as month'));

        if ($salesPermission != 'manage') {
            $monthlySale->where('users.id', $userId);
        }

        return $monthlySale->get();

    }

    public static function monthlyReceive($year)
    {
        $perm = new PermissionController();
        $receivePermission = $perm->checkReceivingPermission();
        $userId = Auth()->user()->id;

        $monthlyReceive = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'receiving')
            ->select(DB::raw('sum(orders.total) as receive'), DB::raw('month(orders.date) as month'));

        if ($receivePermission != 'manage') {

            return $monthlyReceive->where('users.id', $userId)->get();
        }

        return $monthlyReceive->get();
    }

    public static function monthlyProfit($year)
    {
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();
        $userId = Auth()->user()->id;

        $monthlyProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->select(DB::raw('sum(orders.profit) as profit'), DB::raw('month(orders.date) as month'));

        if ($profitPermission != 'manage') {

            return $monthlyProfit->where('users.id', $userId)->get();
        }

        return $monthlyProfit->get();
    }

    public static function salesItems($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkSalesPermission();

        $query = OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('branches', 'branches.id', 'orders.transfer_branch_id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->select('orders.id', 'branches.name as transfer_branch_name', 'orders.date', 'orders.type', 'orders.sub_total', 'orders.total_tax as tax', 'orders.total', 'orders.invoice_id', 'orders.due_amount',
                'payments.payment_method', 'payments.paid', 'payment_types.type as payment_type',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by"),
                DB::raw("users.id as user_id"),
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer"),
                DB::raw("customers.id as customer_id"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))+ 
                (select abs(order_items.sub_total) from order_items where order_items.type ='discount' and order_items.order_id = orders.id)) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_purchased')
            )
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')
            ->groupBy('order_items.order_id');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $query->where('orders.type', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "sales_type") {
                    $query->where('orders.type', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {
                    if ($singleFilter['value'] == 'paid') {
                        $query->where('orders.due_amount', '=', 0);
                    } elseif ($singleFilter['value'] == 'due') {
                        $query->where('orders.due_amount', '>', 0);
                    }
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('customers.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('customers.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
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

    public static function salesSummary($filterKey, $limit, $offset, $groupBy, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::select('order_items.tax_id',
            DB::raw('abs(sum(order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)) as sub_total'),
            DB::raw('abs(sum((order_items.quantity*order_items.price -(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            DB::raw("(sum((abs(order_items.quantity)*order_items.price)* order_items.discount)/100) AS discount"),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
            $filterKey)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->where('orders.order_type', 'sales')
            ->groupBy($groupBy)
            ->orderBy($columnName, $columnSortedBy);

        $allData = $query->get();
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $allData;
        }
    }

    public static function salesSummaryTypeFilter($filterKey, $limit, $offset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::select('order_items.tax_id', $joinColumn1,
            DB::raw('abs(sum(order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)) as sub_total'),
            DB::raw('abs(sum((order_items.quantity*order_items.price -(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            DB::raw("(sum((abs(order_items.quantity)*order_items.price)* order_items.discount)/100) AS discount"),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
            $filterKey)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin($joinTable, $joinColumn1, '=', $joinColumn2)
            ->where('orders.order_type', 'sales');

        if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
            $query->whereBetween('orders.date', [$starts, $ends]);
        }

        if ($branchId > 0) {
            $query->where('orders.branch_id', $branchId);
        }

        $query->groupBy($groupBy)->orderBy($columnName, $columnSortedBy);
        $allData = $query->get();
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $allData;
        }
    }

    public static function orderType($id)
    {
        return OrderItems::getFirst('*', 'id', $id);
    }

    public static function getOrderDetails($id, $invoice = false)
    {
        if ($invoice) $subTotal = "order_items.sub_total";
        else $subTotal = DB::raw('abs(order_items.sub_total) as total');

        return OrderItems::leftjoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('order_items.order_id', '=', $id)
            ->select('order_items.price', 'order_items.type', $subTotal,
                DB::raw('(CASE WHEN order_items.product_id = 0
                THEN  "Discount" ELSE concat(title,if(variant_title="default_variant"," ",concat("(",product_variants.variant_title,")"))) END) as title'),
                DB::raw('abs(order_items.quantity) as quantity'),
                'order_items.discount'
            )->get();
    }

    public static function getOrderDetailsForUpdate($id, $invoice = false)
    {
        if ($invoice) $subTotal = "order_items.sub_total";
        else $subTotal = DB::raw('abs(order_items.sub_total) as total');

        return OrderItems::leftjoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('order_items.order_id', '=', $id)
            ->select('order_items.id', 'order_items.price', 'order_items.type', $subTotal,
                DB::raw('(CASE WHEN order_items.product_id = 0
                THEN  "Discount" ELSE concat(title,if(variant_title="default_variant"," ",concat("(",product_variants.variant_title,")"))) END) as title'),
                DB::raw('abs(order_items.quantity) as quantity'),
                'order_items.discount', 'order_items.order_id'
            )->get();
    }

    public static function getStockInProductDetailsForUpdate($id, $invoice = false)
    {
        if ($invoice) $subTotal = "order_items.sub_total";
        else $subTotal = DB::raw('abs(order_items.sub_total) as total');

        return OrderItems::leftjoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('products.id', '=', $id)
            ->where('order_items.type', '=', 'receiving')
            ->select('order_items.id', 'order_items.price', 'order_items.type', $subTotal,
                DB::raw('(CASE WHEN order_items.product_id = 0
                THEN  "Discount" ELSE concat(title,if(variant_title="default_variant"," ",concat("(",product_variants.variant_title,")"))) END) as title'),
                DB::raw('abs(order_items.quantity) as quantity'),
                'order_items.discount', 'order_items.order_id'
            )->get();
    }


    public static function receivingItems($filtersData, $searchValue, $columnName, $request, $limit, $offset, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkReceivingPermission();

        $query = OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->select('orders.id', 'orders.date', 'orders.type', 'orders.total', 'orders.invoice_id', 'orders.due_amount',
                DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS full_name"),
                DB::raw("users.id as user_id"))
            ->where('orders.order_type', '=', 'receiving')
            ->groupBy('order_items.order_id');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "receive_type") {
                    $query->where('orders.type', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->whereBetween('orders.date', [$singleFilter['value'][0]['start'], $singleFilter['value'][0]['end']]);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {

                    if ($singleFilter['value'] == 'paid') {
                        $query->where('orders.due_amount', '=', 0);
                    } elseif ($singleFilter['value'] == 'due') {
                        $query->where('orders.due_amount', '>', 0);
                    }
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%');
            });
        }

        $allData = $query->orderBy($columnName, $request)->get();


        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $allData;
        }
    }

    public static function receiveSummary($filterKey, $limit, $offset, $groupBy, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::select('order_items.tax_id',
            DB::raw('sum(order_items.sub_total) as sub_total'),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_receive'),
            DB::raw('abs(sum((order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            $filterKey)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->where('orders.order_type', 'receiving')
            ->groupBy($groupBy)
            ->orderBy($columnName, $columnSortedBy);

        $allData = $query->get();

        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $allData;
        }
    }

    public static function receiveSummaryFilter($filterKey, $limit, $offset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::select('order_items.tax_id', $joinColumn1,
            DB::raw('sum(order_items.sub_total) as sub_total'),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_receive'),
            DB::raw('abs(sum((order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            $filterKey)
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin($joinTable, $joinColumn1, '=', $joinColumn2)
            ->where('orders.order_type', 'receiving');

        if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
            $query->whereBetween('orders.date', [$starts, $ends]);
        }

        if ($branchId > 0) {
            $query->where('orders.branch_id', $branchId);
        }
        $query->groupBy($groupBy)->orderBy($columnName, $columnSortedBy);
        $allData = $query->get();

        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {
            return $allData;
        }
    }

    public static function availableQuantity($variantId)
    {
        return OrderItems::where('variant_id', $variantId)->sum('quantity');
    }

    public static function userSalesRecord($year, $id)
    {
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', 'done')
            ->where('orders.order_type', 'sales')
            ->where('orders.created_by', $id)
            ->select(DB::raw('sum(orders.total) as sales'), DB::raw('month(orders.date) as month'));

        if ($salesPermission != 'manage') {
            $monthlySale->where('users.id', $id);
        }

        return $monthlySale->get();
    }

    public static function getItemDetailsforInvoice($orderId)
    {
        return OrderItems::leftjoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->select('product_variants.variant_title', 'products.title', 'order_items.price', 'order_items.discount', 'order_items.sub_total', DB::raw('abs(order_items.quantity) as quantity'))
            ->where('order_id', $orderId)->where('type', '!=', 'discount')
            ->get();
    }

    public static function getDiscountAmount($orderId)
    {
        return OrderItems::select(DB::raw('abs(sub_total) as overAllDiscount'))->where('type', 'discount')->where('order_id', $orderId)->first();
    }
}
