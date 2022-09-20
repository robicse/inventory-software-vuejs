@inject('perm', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', 'Sales')

@section('content')
    <sales-or-receives-component :user="{{ Auth::user() }}" :total_branch="{{$totalBranch}}" :order_type ="'sales'" :sold_to ="'sold_to'" :sold_by ="'sold_by'" current_branch="{{$currentBranch}}" current_cash_register="{{$currentCashRegister}}" addcustomer="{{$perm->customersManagePermission()}}"  manage_price="{{$perm->salesPriceManagePermission()}}" sales_return_status ="{{$salesReturnStatus}}" sales_receiving_type = "{{$salesType}}" branches = "{{$branches}}" >

    </sales-or-receives-component>

@endsection