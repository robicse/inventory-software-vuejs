<?php

namespace App\Models;


use App\Http\Controllers\API\InvoiceTemplateController;

class InvoiceTemplate extends BaseModel
{
    protected $fillable = ['template_type', 'template_title', 'default_content', 'custom_content'];

    public static function getInvoiceTemplate($request)
    {
        return InvoiceTemplate::orderBy($request->columnKey, $request->columnSortedBy)->get();
    }

    public static function getInvoiceTemplateToPrint($cashRegisterId,$orderType)
    {
        $cashRagister =  CashRegister::find($cashRegisterId);

        if ($orderType == 'sales'){
            $invoice =  InvoiceTemplate::find($cashRagister->sales_invoice_id);

        }else{
            $invoice =  InvoiceTemplate::find($cashRagister->receiving_invoice_id);
        }

        if ($invoice->custom_content != ''){
            return $invoice->custom_content;
        }else{
            return $invoice->default_content;
        }

    }
}
