<?php

namespace App\Models;

use Auth;

class Branch extends BaseModel
{
    protected $fillable = ['name', 'is_default', 'tax_id'];

    public static function getBranch($branchId)
    {
        return Branch::select('*')->whereIn('id', explode(',', $branchId))->get();
    }

    public static function getBranchList($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = Branch::count();
        $data = Branch::select('*', 'branches.id as branch_id')->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function setDefaultTax($id)
    {
        Branch::where('is_default', 1, ['tax_id' => $id]);
    }

    public static function getInternalSalesBranch($searchValue,$id)
    {

       return  Branch::where('name', 'LIKE', '%' . $searchValue . '%')->where('id','!=',$id)->get();
    }
}
