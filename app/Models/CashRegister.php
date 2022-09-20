<?php

namespace App\Models;

class CashRegister extends BaseModel
{
    protected $fillable = ['title', 'branch_id', 'sales_invoice_id','receiving_invoice_id','created_by'];

    public static function getRegisters($columnName,$columnSortedBy,$limit,$offset)
    {
        $count = CashRegister::count();
        $data =  CashRegister::join('branches', 'cash_registers.branch_id', '=', 'branches.id')
            ->select('cash_registers.*', 'branches.name as branch_name')
            ->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function getTotals($id)
    {
        return CashRegister::where('branch_id', $id)->count();
    }

    public static function getList($branchId)
    {
      return CashRegister::select('id','title','branch_id as branchID','multiple_access')->where('branch_id',$branchId)->get();
    }

    public static function getCashRegisters($id)
    {
       return CashRegister::select('id','title','branch_id as branchID')->where('id',$id)->first();
    }
}
