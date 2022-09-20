<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Libraries\Permissions;
use App\Libraries\Email;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\ShortcutKey;
use App\Models\Tax;
use App\Models\CustomUser;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Notification;
use App\User;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\API\BranchController;
use phpDocumentor\Reflection\Types\Array_;
use PDF;
use Config;

class SalesController extends Controller
{
    public function permissionCheck()
    {
        $controller = new Permissions;
        return $controller;
    }

    public function index()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);
        $cashRegisterID = $this->getCashRegisterID();

        $salesReturnStatus = Setting::getSettingValue('sales_return_status')->setting_value;
        $salesType = Setting::getSaleOrReceivingType('sales_type');

        return view('sales.SalesIndex', ['currentBranch' => $allSettings->getCurrentBranch(), 'totalBranch' => $totalBranch, 'currentCashRegister' => $cashRegisterID, 'salesReturnStatus' => $salesReturnStatus, 'salesType' => $salesType, 'branches' => $getBranch]);
    }

    public function getRegisterAmount($id)
    {

        return CashRegisterLog::getRegisterAmount($id);

    }

    public function getCashRegisterID()
    {
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $currentBranchID = 0;

        if ($currentBranch) {
            $currentBranchID = $currentBranch->setting_value;
        }

        $cashRegisterID = CashRegisterLog::getRegistersLog($currentBranchID, $userID);

        if ($cashRegisterID) {
            return $cashRegisterID = CashRegister::getCashRegisters($cashRegisterID->cash_register_id);
        } else {
            return $cashRegisterID = null;
        }
    }

    public function orderReceive()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);
        $cashRegisterID = $this->getCashRegisterID();
        $receivingType = Setting::getSaleOrReceivingType('receiving_type');
        return view('receives.ReceivesIndex', ['currentBranch' => $allSettings->getCurrentBranch(), 'totalBranch' => $totalBranch, 'currentCashRegister' => $cashRegisterID, 'receivingType' => $receivingType]);
    }

    public function getReturnProduct(Request $request)
    {
        $orderId = $request->orderId;

        $orderItems = Order::searchOrders($orderId);

        foreach ($orderItems as $rowOrderItem) {
            $rowOrderItem->cart = OrderItems::getAll(['price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);
            foreach ($rowOrderItem->cart as $rowItem) {
                if ($rowItem->taxID) {
                    $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                } else {
                    $rowItem->productTaxPercentage = 0;
                }
                if ($rowItem->variantID != null) {
                    $rowItem->variantTitle = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID)->variant_title;
                    $rowItem->productTitle = Product::getFirst('title', 'id', $rowItem->productID)->title;
                }

                $rowItem->showItemCollapse = false;
                $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
            }

            if ($rowOrderItem->customer != null) {
                $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id'], 'id', $rowOrderItem->customer);
                $rowOrderItem->customer->customer_group_discount = 0;
            }
        }

        return $orderItems;

    }

    public function setSalesReturnsType(Request $request)
    {
        $salesReturnType = $request->salesOrReturnType;
        Setting::updateSetting('sales_return_status', $salesReturnType);
    }

    public function getProduct(Request $request)
    {
        $searchValue = $request->searchValue;
        $orderType = $request->order_type;

        $shortcutSettings = $this->getShortcutSettings();
        $appName = Setting::getSettingValue('app_name');

        if (!$searchValue) {
            $data = Product::index(['products.id as productID', 'products.title', 'products.taxable', 'products.tax_type', 'products.tax_id', 'products.imageURL as productImage', 'products.branch_id']);

        } else {
            $barCodeSearxhResult = $this->barCodeSearch($searchValue, $orderType);

            if ($barCodeSearxhResult) {
                return [
                    'products' => [],
                    'barcodeResultValue' => $barCodeSearxhResult,
                    'shortcutKeyCollection' => $shortcutSettings,
                    'appName' => $appName
                ];
            }

            $data = Product::getProducts($searchValue);
        }

        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID)->setting_value;

        foreach ($data as $rowData) {

            if ($rowData->taxable == 0) {
                $rowData->taxPercentage = 0;
            } else {

                if ($rowData->tax_type == 'default') {

                    $branchTax = Branch::getFirst('*', 'id', $currentBranch);
                    if ($branchTax->taxable == 0) {
                        $rowData->taxPercentage = 0;
                    } else {

                        if ($branchTax->is_default == 0) {
                            $taxID = $branchTax->tax_id;
                        } else {
                            $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                        }

                        $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                    }
                } else {
                    $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $rowData->tax_id)->percentage;
                }
            }

            $productVariant = ProductVariant::getProductVariant($rowData->productID, $orderType);

            foreach ($productVariant as $rowProductVariant) {
                $rowProductVariant->attribute_values = explode(',', $rowProductVariant->attribute_values);
                $rowProductVariant->availableQuantity = OrderItems::availableQuantity($rowProductVariant->id);
            }

            $attribute_name = [];
            $attribute_id = ProductAttributeValue::attributeValues($rowData->productID);

            foreach ($attribute_id as $key => $rowAttributeId) {
                $attribute_name[$key] = ProductAttribute::getFirst('name', 'id', $rowAttributeId->attribute_id)->name;

            }

            $rowData->variants = $productVariant;
            $rowData->attributeName = $attribute_name;
        }

        return [
            'products' => $data,
            'barcodeResultValue' => null,
            'shortcutKeyCollection' => $shortcutSettings,
            'appName' => $appName
        ];
    }

    private function getShortcutSettings()
    {
        $allKeyboardShortcut = ShortcutKey::allData();
        return ['allKeyboardShortcut' => $allKeyboardShortcut];
    }

    public function barCodeSearch($searchValueForBarCode, $orderType)
    {
        $barCodeSearch = ProductVariant::searchProduct($searchValueForBarCode, $orderType);

        if ($barCodeSearch) {
            $barCodeSearch->cartItemNote = '';
            $barCodeSearch->discount = 0;
            $barCodeSearch->quantity = 1;
            $barCodeSearch->showItemCollapse = false;
            $barCodeSearch->discountType = '%';
            $barCodeSearch->calculatedPrice = $barCodeSearch->price;

            if ($barCodeSearch->taxable == 0) {
                $barCodeSearch->productTaxPercentage = 0;
            } else {
                if ($barCodeSearch->tax_type == 'default') {
                    $branchTax = Branch::getFirst('is_default', 'id', $barCodeSearch->branch_id)->is_default;

                    if ($branchTax == 0) {
                        $taxID = Branch::getFirst('tax_id', 'id', $barCodeSearch->branch_id)->tax_id;
                    } else {
                        $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                    }
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                } else {
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $barCodeSearch->taxID)->percentage;
                }
            }
        }

        unset($barCodeSearch->tax_type);
        unset($barCodeSearch->taxable);
        return $barCodeSearch;
    }

    public function setBranch(Request $request)
    {
        $allSetting = new AllSettingFormat;
        $authID = Auth::user('id')->id;
        $branchID = $request->branchID;
        $currentBranch = $allSetting->getCurrentBranch();
        if ($currentBranch) {
            Setting::updateCurrentBranch($authID, $branchID);
        } else {
            Setting::store([
                'setting_name' => 'current_branch',
                'setting_value' => $branchID,
                'setting_type' => 'user',
                'user_id' => $authID,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function salesStore(Request $request)
    {
        $cashRegister = $request->cashRagisterId;
        $allSettings = new AllSettingFormat;
        $userId = Auth::id();
        $userBranchId = Setting::getFirst('*', 'user_id', $userId)->setting_value;
        $date = Carbon::now()->toDateString();
        $orderType = $request->orderType;
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderStatus = $request->status;
        $createdBy = Auth::user()->id;
        $carts = $request->cart;
        $id = $request->customer['id'];
        $subTotal = $request->subTotal;
        $tax = $request->tax;
        $allDiscount = $request->discount;
        $grandTotal = $request->grandTotal;
        $payment = $request->payments;
        $orderID = $request->orderID;
        $transferBranch = $request->transferBranch;
        $dueAmount = 0;
        $time = $request->time;

        if ($request->profit == null) {
            $profit = 0;
        } else {
            $profit = $request->profit;
        }

        $invoiceFixes = $allSettings->getInvoiceFixes();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;

        } else {
            $cashRegisterID = null;
        }

        if ($allDiscount == null) {
            $allDiscount = 0;
        }

        if (!empty($payment)) {
            foreach ($payment as $key => $value) {
                if ($value['paymentType'] == 'credit') {
                    $dueAmount = floatval($value['paid']);
                }
            }
        }

        if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID) || ($orderStatus == 'hold' && !$orderID)) {

            $orderData = array();
            $orderData['date'] = $date;
            $orderData['order_type'] = $orderType;

            $orderData['all_discount'] = $allDiscount;

            $orderData['sub_total'] = $subTotal;
            $orderData['total_tax'] = $tax;
            $orderData['due_amount'] = $dueAmount;
            $orderData['total'] = $grandTotal;
            $orderData['type'] = $salesOrReceivingType;
            $orderData['profit'] = $profit;
            $orderData['status'] = $orderStatus;

            if ($salesOrReceivingType == 'internal') {
                $orderData['transfer_branch_id'] = $transferBranch;
            }

            if ($orderType == 'sales') {
                $orderData['customer_id'] = $id;
            } else {
                $orderData['supplier_id'] = $id;
            }

            $orderData['created_by'] = $createdBy;
            $orderData['branch_id'] = $userBranchId;
            $orderData['created_at'] = Carbon::parse($time);

            $orderLastId = Order::store($orderData);
            $orderID = $orderLastId->id;

            Order::updateData($orderID, ['invoice_id' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix']]);
            Setting::updateSetting('last_invoice_number', $invoiceFixes['lastInvoiceNumber'] + 1);


        } else {
            $orders = array();
            $orders['date'] = $date;
            $orders['order_type'] = $orderType;

            $orders['all_discount'] = $allDiscount;

            $orders['sub_total'] = $subTotal;
            $orders['total_tax'] = $tax;
            $orders['total'] = $grandTotal;
            $orders['type'] = $salesOrReceivingType;
            $orders['status'] = $orderStatus;

            if ($salesOrReceivingType == 'internal') {
                $orders['transfer_branch_id'] = $transferBranch;
            }
            if ($orderType == 'sales') {
                $orders['customer_id'] = $id;

            } else {
                $orders['supplier_id'] = $id;
            }
            $orders['created_by'] = $createdBy;

            Order::updateData($request->orderID, $orders);
        }

        $orderItems = [];

        foreach ($carts as $cart) {

            if ($orderType == 'sales') {
                $quantity = -$cart['quantity'];
            } else {
                $quantity = $cart['quantity'];
            }

            if (!array_key_exists('discount', $cart) || $cart['discount'] == null) {
                $cart['discount'] = 0;
            }

            array_push($orderItems, ['product_id' => $cart['productID'],
                'variant_id' => $cart['variantID'],
                'type' => $cart['orderType'],
                'quantity' => $quantity,
                'price' => $cart['price'],
                'discount' => $cart['discount'],
                'sub_total' => $cart["calculatedPrice"],
                'tax_id' => $cart['taxID'],
                'order_id' => $orderID,
                'note' => $cart['cartItemNote'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')]);
        }

        if ($orderStatus != 'hold') {
            if (sizeof($payment) > 0) {
                $paymentArray = [];

                foreach ($payment as $rowPayment) {
                    array_push($paymentArray, ['date' => $date, 'paid' => $rowPayment['paid'], 'exchange' => $rowPayment['exchange'], 'payment_method' => $rowPayment['paymentID'], 'options' => serialize($rowPayment['options']), 'order_id' => $orderID, 'cash_register_id' => $cashRegisterID, 'created_at' => $rowPayment['PaymentTime']]);
                }

                if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID)) {
                    Payments::insertData($paymentArray);
                } else {
                    Payments::deleteRecord('order_id', $request->orderID);
                    Payments::insertData($paymentArray);
                }
            }
        }
        if (($orderStatus == 'done' && $orderID == null)) {
            OrderItems::insertData($orderItems);
            $response = [
                'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['prefix'],
            ];
        } else if (($orderStatus == 'pending' && $orderID == null)) {
            OrderItems::insertData($orderItems);
            $response = [
                'orderID' => $orderID
            ];

            return $response;
        } else {
            OrderItems::deleteRecord('order_id', $request->orderID);
            OrderItems::insertData($orderItems);

            if ($orderStatus == 'done') {

//            send customer invoice
                try {
                    $invoiceTemplateEmail = new InvoiceTemplateController();
                    $invoiceTemplateData = $invoiceTemplateEmail->getInvoiceTemplateToPrint($orderID, $cashRegister, $orderType, 'email');
                    $autoEmailReceive = Setting::getSettingValue('auto_email_receive')->setting_value;
                    $orderDetails = Order::orderDetails($orderID);
                    if ($orderDetails->customer_id) {
                        $orderCustomer = Customer::getOne($orderDetails->customer_id);

                        if ($autoEmailReceive == 1 && $orderCustomer->email) {

                            $content = EmailTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_invoice')->first();
                            $subject = $content->template_subject;
                            if ($content->custom_content) {
                                $text = $content->custom_content;
                            } else {
                                $text = $content->default_content;
                            }

                            $mailText = str_replace('{first_name}', $orderCustomer->first_name, str_replace('{invoice_id}', $orderDetails->invoice_id, str_replace('{app_name}', Config::get('app_name'), $text)));


                            $this->sendPdf($invoiceTemplateData['data'], $orderID, $mailText, $orderCustomer->email, $subject);
                        }
                    }
                } catch (\Exception $e) {
                }

                $invoiceTemplate = new InvoiceTemplateController();
                $templateData = $invoiceTemplate->getInvoiceTemplateToPrint($orderID, $cashRegister, $orderType, 'receipt');

                $response = [
                    'orderID' => $orderID,
                    'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'],
                    'message' => Lang::get('lang.payment_done_successfully'),
                    'invoiceTemplate' => $templateData
                ];

                return $response;
            } else {
                $response = [
                    'orderID' => $orderID,
                    'invoiceID' => $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'],
                    'message' => Lang::get('lang.payment_done_successfully'),
                ];

                return $response;
            }
        }
    }

    public function saveDueAmount(Request $request)
    {

        $data = $request->cartItemsToStore;

        $orderId = $data['rowData']['id'];
        $paymentType = $data['paymentType'];
        $date = Carbon::now()->toDateString();
        $payments = $data['payments'];
        $cashRegisterID = null;
        $output = null;

        $allSettings = new AllSettingFormat;
        $userId = Auth::id();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;

        } else {
            $cashRegisterID = null;
        }

        $deleteRow = Payments::destroyByOrderAndType($orderId, $paymentType);

        if (isset($payments)) {
            $paymentArray = [];
            $due = 0;
            foreach ($payments as $rowPayment) {
                array_push($paymentArray,
                    [
                        'date' => $date,
                        'paid' => $rowPayment['paid'],
                        'exchange' => $rowPayment['exchange'],
                        'payment_method' => $rowPayment['paymentID'],
                        'options' => serialize($rowPayment['options']),
                        'order_id' => $orderId,
                        'cash_register_id' => $cashRegisterID,
                        'created_at' => $rowPayment['PaymentTime']
                    ]
                );

                if ($rowPayment['paymentType'] == 'credit') {
                    $due = $rowPayment['paid'];
                }

            }
            $updateData = [
                'due_amount' => $due
            ];
            Order::updateData($orderId, $updateData);
            if (isset($paymentArray)) {
                $output = Payments::insertData($paymentArray);
            }
        }

        if ($output) {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.payment_done_successfully')
            ];
        } else {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.something_went_wrong')
            ];
        }
    }

    public function salesCancel(Request $request)
    {
        $orderId = $request->orderID;

        if (Order::checkExists('id', $orderId)) {
            Order::updateData($orderId, ['status' => 'cancelled']);
        }
    }

    public function getPaymentsAndDetails(Request $request)
    {
        $orderId = $request->orderID;
        $payments = [];

        if ($orderId) {
            $payments = Payments::getAll('*', 'order_id', $orderId);
        }

        return $payments;
    }

    public function customerList(Request $request)
    {
        $searchValue = $request->customerSearchValue;

        if ($request->orderType == 'sales') {

            return Customer::customerData($searchValue);

        } else {
            return Supplier::supplierData($searchValue);
        }


    }

    public function getHoldOrder()
    {
        $orderHoldItems = Order::getHoldOrders();

        foreach ($orderHoldItems as $rowOrderItem) {
            $rowOrderItem->cart = OrderItems::getAll(['price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);
            foreach ($rowOrderItem->cart as $rowItem) {
                if ($rowItem->taxID) {
                    $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                } else {
                    $rowItem->productTaxPercentage = 0;
                }
                if ($rowItem->variantID != null && $rowItem->variantID != 0) {
                    $rowItem->variantTitle = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID)->variant_title;
                    $rowItem->productTitle = Product::getFirst('title', 'id', $rowItem->productID)->title;
                }

                $rowItem->quantity = abs($rowItem->quantity);
                $rowItem->showItemCollapse = false;
                $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
            }

            if ($rowOrderItem->customer != null) {
                $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id',], 'id', $rowOrderItem->customer);
                $rowOrderItem->customer->customer_group_discount = 0;
            }
        }

        return $orderHoldItems;
    }


    public function sendPdf($templateData, $orderID, $mailText, $email, $subject)
    {
        try {
            $allSettingFormat = new AllSettingFormat();
            $order = $this->formatOrdersDetails($orderID);
            $order->due = $allSettingFormat->getCurrencySeparator($order->due);
            $orderItems = $this->formatOrdersItems($orderID);
            $appName = Config::get('app_name');
            $invoiceLogo = Config::get('invoiceLogo');
            $fileNameToStore = 'Gain-' . $order->invoice_id . '.pdf';
            $pdf = PDF::loadView('invoice.invoiceTemplate', compact('templateData', 'orderItems', 'order', 'appName', 'invoiceLogo'));
            $pdf->save('uploads/pdf/' . $fileNameToStore);
            $emailSend = new Email;
            $emailSend->sendEmail($mailText, $email, $subject, $fileNameToStore);
            unlink(public_path('uploads/pdf/' . $fileNameToStore));
        } catch (\Exception $e) {
        }
    }

    public function formatOrdersDetails($orderID)
    {
        $orderDetails = Order::getInvoiceData($orderID);
        $allSettingFormat = new AllSettingFormat();
        $orderDetails->due = $orderDetails->total - $orderDetails->paid;

        $orderDetails->paid = $allSettingFormat->getCurrencySeparator($orderDetails->paid);
        $orderDetails->total = $allSettingFormat->getCurrencySeparator($orderDetails->total);
        $orderDetails->sub_total = $allSettingFormat->getCurrencySeparator($orderDetails->sub_total);
        $orderDetails->change = $allSettingFormat->getCurrencySeparator($orderDetails->change);
        $orderDetails->date = $allSettingFormat->getDate($orderDetails->date);


        if ($orderDetails->change == null) {
            $orderDetails->change = 0;
        }

        return $orderDetails;
    }

    public static function formatOrdersItems($orderID)
    {
        $orderItems = OrderItems::getOrderDetails($orderID, true);
        $allSettingFormat = new AllSettingFormat();
        foreach ($orderItems as $item) {
            if ($item->type == 'discount') {
                $item->price = null;
                $item->quantity = null;
                $item->discount = null;
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            } else {
                $item->discount = $item->discount . '%';
                $item->price = $allSettingFormat->getCurrencySeparator($item->price);
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            }
        }

        return $orderItems;
    }

    public function setSalesReceivingType(Request $request)
    {
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderType = $request->orderType;
        Setting::saveSalesOrReceivingType($salesOrReceivingType, $orderType);
    }

    public function deleteSale($id)
    {
        Order::deleteData($id);
        OrderItems::deleteRecord('order_id', $id);
        $response = [
            'message' => Lang::get('lang.order') . ' ' . Lang::get('lang.deleted_successfully'),
        ];
        return response()->json($response, 200);
    }

}