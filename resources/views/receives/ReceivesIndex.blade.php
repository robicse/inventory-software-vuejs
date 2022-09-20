@inject('perm', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', 'Receives')

@section('content')


    <sales-or-receives-component :user="{{ Auth::user() }}" :order_type ="'receiving'" :total_branch="{{$totalBranch}}"  :sold_to ="'receiving_to'" :sold_by ="'receiving_by'" current_branch="{{$currentBranch}}" current_cash_register="{{$currentCashRegister}}" addcustomer="{{$perm->customersManagePermission()}}" sales_receiving_type = "{{$receivingType}}"  >

    </sales-or-receives-component>

@endsection