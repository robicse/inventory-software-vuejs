<?php

namespace App\Http\Controllers\API;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Libraries\AllSettingFormat;
use DB;

class BranchController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        if ($authUser->is_admin == 1) {
            $data = Branch::index('*');
        } else {
            $branchId = $authUser->branch_id;
            $data = Branch::getBranch($branchId);
        }

        return $data;
    }

    public function getAllBranches()
    {
        return Branch::allData();
    }

    public function getRowBranch($id)
    {
        return Branch::getOne($id);
    }

    public function getBranchList(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $branch = Branch::getBranchList($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        foreach ($branch['data'] as $rowBranch) {
            $products = Branch::getOne($rowBranch->id);

            if ($products->exists()) {

                $rowBranch->used = 1;
            }
        }

        return ['datarows' => $branch['data'], 'count' => $branch['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'tax_id' => 'required',
            'isCashRegisterUser' => 'required',
        ]);

        $tax_id = $request->tax_id;
        $data = array();
        $data['name'] = $request->name;
        $data['is_cash_register'] = $request->isCashRegisterUser;
        $data['created_by'] = Auth::user()->id;

        if ($tax_id == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($tax_id == 'default-tax') {
            $data['taxable'] = 1;
            $data['is_default'] = 1;
            $taxRowCount = Tax::getTotals();

            if ($taxRowCount == 0) {
                $response = [
                    'message' => Lang::get('lang.no_tax_added_default')
                ];

                return response()->json($response, 404);
            } else {
                $tax = Tax::getId();
                $data['tax_id'] = $tax;
            }
        } else {
            $data['taxable'] = 1;
            $data['is_default'] = 0;
            $data['tax_id'] = $tax_id;
        }

        $branch_id = Branch::getInsertedId($data);

        if ($branch_id) {
            CashRegister::store([
                "title" => 'Main Cash Register',
                "branch_id" => $branch_id,
                "sales_invoice_id" => 2,
                "receiving_invoice_id" => 4,
                "created_by" => Auth::user()->id
            ]);
            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_added')
            ];

            return response()->json($response, 200);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'tax_id' => 'required',
            'isCashRegisterUser' => 'required',
        ]);

        $tax_id = $request->tax_id;
        $data = array();
        $data['name'] = $request->name;
        $data['is_cash_register'] = $request->isCashRegisterUser;
        $data['created_by'] = Auth::user()->id;

        if ($tax_id == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($tax_id == 'default-tax') {
            $data['taxable'] = 1;
            $data['is_default'] = 1;
            $taxRowCount = Tax::getTotals();

            if ($taxRowCount == 0) {
                $response = [
                    'message' => Lang::get('lang.no_tax_added_default')
                ];

                return response()->json($response, 404);
            } else {
                $tax = Tax::getId();
                $data['tax_id'] = $tax;
            }
        } else {
            $data['taxable'] = 1;
            $data['is_default'] = 0;
            $data['tax_id'] = $tax_id;
        }
        Branch::updateData($id, $data);

        $response = [
            'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_updated')
        ];

        return response()->json($response, 200);
    }

    public function deleteBranch($id)
    {
        $used = CashRegister::getTotals($id);

        if ($used == 0) {
            Branch::deleteData($id);

            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.branch'))
            ];

            return response()->json($response, 200);
        }
    }

    public function branchList(){
        return Branch::index(['name as text', 'id as value']);
    }

    public function getInternalSalesBranch(Request $request){
        $searchValue = $request->searchValue;
        $branchId = $request->branchId;
         return Branch::getInternalSalesBranch($searchValue,$branchId);
    }
}
