<?php

namespace App\Http\Controllers\API;

use App\Libraries\ProductData;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Tax;
use Carbon\Carbon;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductGroup;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttribute;
use App\Models\OrderItems;
use App\Libraries\AllSettingFormat;
use App\Libraries\imageHandler;

class ProductsController extends Controller
{
    public function permissionCheck()
    {
        $controller = new Permissions;
        return $controller;
    }

    public function index()
    {
        $tabName = '';
        $routeName = '';
        if(isset($_GET['tab_name'])){
            $tabName = $_GET['tab_name'];
        }
        if(isset($_GET['route_name'])){
            $routeName = $_GET['route_name'];
        }
        return view('products.ProductsIndex',['tab_name'=>$tabName, 'route_name'=>$routeName]);

    }

    public function getProduct(Request $request)
    {
        $products = Product::getAllData($request);
        $request->reqType = 'barcode';
        $allProducts = Product::getAllData($request);

        return ['datarows' => $products['row'], 'allProducts' => $allProducts['row'], 'count' => $products['count']];
    }

    public function showVariant($id)
    {
        return ProductVariant::getFirst('*', 'product_id', $id);
    }

    public function productSupportingData()
    {
        return [
            'brands' => ProductBrand::allData(),
            'categories' => ProductCategory::allData(),
            'groups' => ProductGroup::allData(),
            'taxes' => Tax::allData(),
            'branches' => Branch::allData(),
            'units' => ProductUnit::allData()
        ];
    }

    public function storeProduct(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'sallingPrice' => 'required',
            'receivingPrice' => 'required',
            'type' => 'required',
        ]);
        //die(var_dump($request->all()));

        $name = $request->name;
        $category = $request->category;
        $brand = $request->brand;
        $group = $request->group;
        $unit = $request->unit;
        $expireddate = $request->expireddate;
        $taxID = $request->taxID;
        $image = $request->image;
        $fileNameToStore = 'no_image.png';
        if ($request->type == 0) {
            $product_type = 'standard';
        } else {
            $product_type = 'variant';
        }
        $created_by = Auth::user()->id;
        $imageHandler = new imageHandler;
        if ($file = $image) {
            $fileNameToStore = $imageHandler->imageUpload($image, 'product_', 'uploads/products/');
        }
        if ($taxID == 'no-tax') {
            $productId = Product::store([
                'title' => $name,
                'category_id' => $category,
                'brand_id' => $brand,
                'group_id' => $group,
                'unit_id' => $unit,
                'expireddate' => $expireddate,
                'taxable' => 0,
                'product_type' => $product_type,
                'imageURL' => $fileNameToStore,
                'created_by' => $created_by
            ]);
        } else if ($taxID == 'default-tax') {
            $productId = Product::store([
                'title' => $name,
                'category_id' => $category,
                'brand_id' => $brand,
                'group_id' => $group,
                'unit_id' => $unit,
                'expireddate' => $expireddate,
                'taxable' => 1,
                'tax_type' => 'default',
                'product_type' => $product_type,
                'imageURL' => $fileNameToStore,
                'created_by' => $created_by
            ]);
        } else {
            $productId = Product::store([
                'title' => $name,
                'category_id' => $category,
                'brand_id' => $brand,
                'group_id' => $group,
                'unit_id' => $unit,
                'expireddate' => $expireddate,
                'taxable' => 1,
                'tax_type' => 'custom',
                'tax_id' => $taxID,
                'product_type' => $product_type,
                'imageURL' => $fileNameToStore,
                'created_by' => $created_by
            ]);
        }
        $this->variantInsertion($request, $productId);
        $response = [
            'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_added')
        ];
        return response()->json($response, 201);
    }

    private function variantInsertion($request, $productId)
    {
        $expired_date = $request->expireddate;
        $variant = $request->variant;
        $sallingPrice = $request->sallingPrice;
        $receivingPrice = $request->receivingPrice;
        $sku = $request->sku;
        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;

        $reorder = $request->reorder;
        $barcode = $request->barcode;
        $enabled = $request->enabled;
        $variantDetails = $request->variantDetails;
        $chipValue = $request->chipValues;
        $variantImage = $request->variantImage;
        $imageHandler = new imageHandler;
        if (!empty($request->variant)) {
            $variantAttributeInsertValue = $this->variantAttributeInsertValues($chipValue, $productId->id);
            ProductAttributeValue::insertData($variantAttributeInsertValue);
            $variantInsertValue = [];
            for ($i = 0; $i < sizeof($variant); $i++) {
                $data = isset($variantImage[$i]) ? $variantImage[$i] : false;
                if ($data != null) {
                    $fileNameToStore2 = $imageHandler->imageUpload($request->variantImage[$i], 'product_', 'uploads/products/');
                } else {
                    $fileNameToStore2 = 'no_image.png';
                }
                if (!isset($barcode[$i])) {
                    $barcode[$i] = null;
                }
                if (!isset($sku[$i])) {
                    $sku[$i] = null;
                } else {
                    $sku[$i] = $skuPrefix . $sku[$i];
                }
                array_push($variantInsertValue, [
                    'product_id' => $productId->id,
                    'variant_title' => $variant[$i],
                    'attribute_values' => $variantDetails[$i],
                    're_order' => $reorder[$i],
                    'purchase_price' => $receivingPrice[$i],
                    'selling_price' => $sallingPrice[$i],
                    'expireddate' => $expired_date[$i],
                    'enabled' => $enabled[$i],
                    'imageURL' => $fileNameToStore2,
                    'bar_code' => $barcode[$i],
                    'sku' => $sku[$i]
                ]);
            }
            return ProductVariant::insertData($variantInsertValue);

        } else {
            if ($sku != null) {
                $sku = $skuPrefix . $sku;
            }
            return ProductVariant::store(['product_id' => $productId->id, 'variant_title' => 'default_variant', 'attribute_values' => 'default_variant', 'purchase_price' => $receivingPrice, 'selling_price' => $sallingPrice, 'sku' => $sku, 're_order' => $reorder, 'expireddate' => $expired_date, 'bar_code' => $barcode, 'enabled' => $enabled]);
        }
    }

    private function variantAttributeInsertValues($data, $id)
    {
        $chipValue = $data;
        $variantAttributeInsertValue = [];
        for ($i = 0; $i < sizeof($chipValue); $i++) {
            $attributeValue = '';
            if ($chipValue[$i] != null) {
                for ($j = 0; $j < sizeof($chipValue[$i]); $j++) {
                    $attributeValue = $attributeValue . ',' . $chipValue[$i][$j];
                }
                array_push($variantAttributeInsertValue, ['product_id' => $id, 'attribute_id' => $i, 'values' => $attributeValue]);
            }
        }
        return $variantAttributeInsertValue;
    }

    public static function getAllBarcode($productId = 0)
    {
        $barcodeList = [];
        $allBarcode = ProductVariant::getSkuBarcode('bar_code', $productId);

        foreach ($allBarcode as $bar_code) {
            array_push($barcodeList, $bar_code->bar_code);
        }

        return ['allBarcode' => $barcodeList];
    }

    public static function getAllSku($productId = 0)
    {
        $skuList = [];
        $allSku = ProductVariant::getSkuBarcode('sku', $productId);

        foreach ($allSku as $sku) {
            array_push($skuList, $sku->sku);
        }
        return ['allSku' => $skuList];
    }

    public function getProductEditData($id, ProductData $data)
    {
        $productDetails = Product::getFirst('*', 'id', $id);
        $variantDetails = ProductVariant::getAll('*', 'product_id', $id);
        $productSupportingData = $data->productSupportingData();
        $defaultReorder = Setting::getSettingValue('re_order')->setting_value;

        if ($productDetails->product_type == 'variant') {
            $variantAttributes = ProductAttributeValue::getById($id);
            $variantData = array();

            foreach ($variantAttributes as $variant) {
                $variantData[$variant->id] = explode(',', $variant->values);
                array_shift($variantData[$variant->id]);
            }

            $productAttributes = ProductAttribute::index(['id', 'name']);
            foreach ($variantDetails as $variant) {
                if ($variant['re_order'] == null) {
                    $variant['re_order'] = $defaultReorder;
                }
            }


            return ['productDetails' => $productDetails, 'variantDetails' => $variantDetails, 'variantData' => $variantData, 'AllAttributesProduct' => $productAttributes, 'productSupportingData' => $productSupportingData, 'defaultReorder' => $defaultReorder, 'getAllBarcode' => $this->getAllBarcode($id), 'getAllSku' => $this->getAllSku($id)];

        } else return ['productDetails' => $productDetails, 'variantDetails' => $variantDetails, 'productSupportingData' => $productSupportingData, 'getAllBarcode' => $this->getAllBarcode($id), 'getAllSku' => $this->getAllSku($id)];

    }

    public function editProduct(Request $request, $id)
    {
        //die(var_dump($request->all()));
        $this->validate($request, [
            'name' => 'required',
            'sallingPrice' => 'required',
            'receivingPrice' => 'required',
            'type' => 'required',
        ]);
        $hasDuplicate = [];
        $imageHandler = new imageHandler;

        if ($request->type == 0) $productType = 'standard';
        else $productType = 'variant';

        $data = array();
        $data['title'] = $request->name;
        $data['category_id'] = $request->category;
        $data['brand_id'] = $request->brand;
        $data['group_id'] = $request->group;
        $data['unit_id'] = $request->unit;
        $data['product_type'] = $productType;
        $data['created_by'] = Auth::user()->id;
        $variantDetails = $request->variantDetails;

        if ($request->taxID == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($request->taxID == 'default-tax') {
            $data['taxable'] = 1;
            $data['tax_type'] = 'default';
        } else {
            $data['taxable'] = 1;
            $data['tax_type'] = 'custom';
            $data['tax_id'] = $request->taxID;
        }
        if (empty($request->image)) Product::updateData($id, $data);
        else {
            $fileNameToStore = $imageHandler->imageUpload($request->image, 'product_', 'uploads/products/');
            $data['imageURL'] = $fileNameToStore;

            Product::updateData($id, $data);
        }

        $variantData['expireddate'] = $request->expireddate;

        if ($request->type == 0) {
            $variantData['purchase_price'] = $request->receivingPrice;
            $variantData['selling_price'] = $request->sallingPrice;
            $variantData['sku'] = $request->sku;
            $variantData['bar_code'] = $request->barcode;
            $variantData['re_order'] = $request->reorder;

            ProductVariant::editData('product_id', $id, $variantData);
        } else {

            $hasDuplicate = $this->updateProductVariant($variantDetails, $request, $id);

            ProductAttributeValue::deleteRecord('product_id', $id);

            $variantAttributeInsertValue = $this->variantAttributeInsertValues($request->chipValues, $id);

            ProductAttributeValue::insertData($variantAttributeInsertValue);
        }
        if ($hasDuplicate != null && (array_key_exists("duplicateSku", $hasDuplicate) || array_key_exists("duplicateBarcode", $hasDuplicate))) {
            $response = [
                'duplicateData' => $hasDuplicate
            ];
            return response()->json($response, 400);
        } else {
            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 200);
        }
    }

    private function updateProductVariant($variantDetails, $request, $id)
    {
        $duplicateData = [];
        $isDuplicate = false;
        $imageHandler = new imageHandler;
        $variantValues = [];
        $duplicateSku = [];
        $duplicateBarcode = [];
        foreach ($variantDetails as $index => $value) {

            if (!empty($request->sku[$index])) {
                $skuExists = ProductVariant::ifExists('sku', $request->sku[$index]);
                if ($skuExists > 1) {
                    $isDuplicate = true;
                    array_push($duplicateSku, $request->sku[$index]);
                }
                $duplicateData['duplicateSku'] = $duplicateSku;
            }

            if (!empty($request->barcode[$index])) {
                $barcodeExists = ProductVariant::ifExists('bar_code', $request->barcode[$index]);
                if ($barcodeExists > 1) {
                    $isDuplicate = true;
                    array_push($duplicateBarcode, $request->barcode[$index]);
                }
                $duplicateData['duplicateBarcode'] = $duplicateBarcode;
            }

            if (!$isDuplicate) {
                $variantValues['variant_title'] = $request->variant[$index];
                $variantValues['attribute_values'] = $variantDetails[$index];

                if (isset($request->receivingPrice[$index])) $variantValues['purchase_price'] = $request->receivingPrice[$index];
                if (isset($request->sallingPrice[$index])) $variantValues['selling_price'] = $request->sallingPrice[$index];

                if (isset($request->sku[$index])) $variantValues['sku'] = $request->sku[$index];
                else $variantValues['sku'] = null;

                if (isset($request->barcode[$index])) $variantValues['bar_code'] = $request->barcode[$index];
                else $variantValues['bar_code'] = null;

                if (isset($request->expireddate[$index])) $variantValues['expireddate'] = $request->expireddate[$index];
                else $variantValues['expireddate'] = null;

                $variantValues['re_order'] = $request->reorder[$index];
                if ($request->enabled[$index] == true) $variantValues['enabled'] = 1;
                else $variantValues['enabled'] = 0;

                if (!empty($request->variantImage[$index])) {
                    $fileNameToStore = $imageHandler->imageUpload($request->variantImage[$index], 'product_', 'uploads/products/');
                    $variantValues['imageURL'] = $fileNameToStore;
                } else {
                    unset($variantValues['imageURL']);
                }

                $productVariantCount = ProductVariant::countRecord('product_id', $id);
                $checkAttribute = ProductVariant::getVariantData($id, $variantDetails[$index]);

                if ($productVariantCount == count($variantDetails)) {

                    $variantValues['product_id'] = $id;
                    ProductVariant::updateData($checkAttribute->id, $variantValues);

                } else {
                    $oldVariantId = null;
                    if (!empty($checkAttribute)) {
                        $oldVariantId = $checkAttribute->id;
                        ProductVariant::deleteRecord('id', $checkAttribute->id);
                    }

                    $variantValues['product_id'] = $id;
                    if (empty($request->variantImage[$index])) $variantValues['imageURL'] = 'no_image.png';
                    $newId = ProductVariant::getInsertedId($variantValues);

                    if ($oldVariantId != null) {
                        OrderItems::updateValue('variant_id', $oldVariantId, ['variant_id' => $newId]);
                    }

                }
            } else return $duplicateData;
        }
    }

    public function deleteProduct($id)
    {
        if (OrderItems::checkExists('product_id', $id)) {
            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.product'))
            ];

            return response()->json($response, 200);
        } else {
            Product::deleteData($id);
            ProductVariant::deleteRecord('product_id', $id);

            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        }
    }

    public function productDetailsShow($id)
    {
        $tabName = '';
        $routeName = '';
        if(isset($_GET['tab_name'])){
            $tabName = $_GET['tab_name'];
        }
        if(isset($_GET['route_name'])){
            $routeName = $_GET['route_name'];
        }
        return view('products.ProductDetails',['id' => $id, 'tab_name'=>$tabName, 'route_name'=>$routeName]);

    }

    public function getProductDetails($id)
    {
        $productDetails = Product::detailsById($id);

        if ($productDetails->tax_type = 'custom') {
            $productDetails->tax_type = Lang::get('lang.customs');
        }

        if ($productDetails->product_type == 'standard') {
            $productDetails->temp_product_type = Lang::get('lang.standard');
            $variant = ProductVariant::getFirst('*', 'product_id', $id);
            $allSettingFormat = new AllSettingFormat;
            $variant->price = $allSettingFormat->getCurrency($variant->price);

            return ['productDetails' => $productDetails, 'variant' => $variant];
        } else {
            $productDetails->temp_product_type = Lang::get('lang.variant');
            return ['productDetails' => $productDetails];
        }
    }

    public function getVariantDetails($id)
    {
        $variant = ProductVariant::getAll('*', 'product_id', $id);
        $count = ProductVariant::countRecord('product_id', $id);

        $allSettingFormat = new AllSettingFormat;
        foreach ($variant as $data) {
            $data->price = $allSettingFormat->getCurrency($data->price);
        }
        return ['datarows' => $variant, 'count' => $count];
    }

    public function importProduct(Request $request)
    {
        $row = 0;
        $invalidData = [];
        $created_by = Auth::user()->id;
        $invalidDataCollection = [];
        $errorPreviewData = [];

        // Validate with correct column name
        $isValid = $this->importFileValidation($request->importData, $request->requiredColumns);

        $defaultReorder = Setting::getSettingValue('re_order')->setting_value;

        DB::beginTransaction();

        if ($isValid == true) {
            foreach ($request->importData as $index => $product) {
                if ($product['RE-ORDER'] == null) {
                    $product['RE-ORDER'] = $defaultReorder;
                }
                //assigning as per DB column
                $productData = [
                    'title' => $product['NAME'],
                    'category_id' => $product['CATEGORY'],
                    'brand_id' => $product['BRAND'],
                    'group_id' => $product['GROUP'],
                    'name' => $product['UNIT'],
                    'short_name' => $product['UNIT_SHORT_NAME'],
                    'product_type' => strtolower($product['PRODUCT_TYPE']),
                    'variant_title' => $product['VARIANT_NAME'],
                    'attribute_value' => $product['VARIANT_VALUE'],
                    'variant_details' => $product['VARIANT_DETAIL'],
                    'sku' => $product['SKU'],
                    'bar_code' => $product['BARCODE'],
                    're_order' => $product['RE-ORDER'],
                    'purchase_price' => $product['PURCHASE-PRICE'],
                    'selling_price' => $product['SELLING-PRICE'],
                    'imageURL' => 'no_image.png',
                    'created_by' => $created_by
                ];
                if (strtolower($product['PRODUCT_TYPE']) == 'standard') {
                    $rowData = $this->importStandardProduct($productData, $index, $request->fill_able);
                    if (gettype($rowData) == "array") array_push($invalidData, $rowData);
                    else $row++;
                } elseif (strtolower($product['PRODUCT_TYPE']) == 'variant') {
                    $rowData = $this->importVariantProduct($productData, $index);
                    if (gettype($rowData) == "array") array_push($invalidData, $rowData);
                    else $row++;
                } else {
                    $errorData['index'] = $index;
                    $errorData['productType'] = $product['PRODUCT_TYPE'];
                    if (ProductVariant::ifExists('product_variants.sku', $productData['sku'])) $errorData['invalidSku'] = $productData['sku'];
                    if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) $errorData['invalidBarcode'] = $productData['bar_code'];
                    array_push($invalidData, $errorData);
                }
            }
        }
        if (sizeof($request->importData) == $row) {
            DB::commit();
            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];
            return response()->json($response, 201);
        } else {
            DB::rollback();
            $showInvalidData = $request->importData;
            foreach ($showInvalidData as $index => $product) {
                foreach ($invalidData as $item) {
                    if ($item['index'] == $index) {
                        $excelErrorData = [];
                        if (array_key_exists("invalidSku", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'SKU' . ': ' . $item['invalidSku'] . ' ');
                        if (array_key_exists("invalidBarcode", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'BARCODE' . ': ' . $item['invalidBarcode']);
                        if (array_key_exists("invalidTitle", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'TITLE' . ': ' . $item['invalidTitle']);
                        if (array_key_exists("invalidData", $item)) array_push($excelErrorData, Lang::get('lang.invalid_data') . ' ' . 'INVALID DATA' . ': ' . $item['invalidData']);
                        if (array_key_exists("emptyData", $item)) array_push($excelErrorData, Lang::get('lang.field_must_not_be_empty'));
                        if (array_key_exists("productType", $item)) array_push($excelErrorData, Lang::get('lang.product_type_must_be_either_standard_or_variant'));
                        $product['INVALID_DATA'] = implode(" ", $excelErrorData);
                    }
                }
                array_push($invalidDataCollection, $product);
            }

            foreach ($invalidData as $data) {
                if (array_key_exists("invalidSku", $data)) array_push($errorPreviewData, $data['invalidSku']);
                if (array_key_exists("invalidBarcode", $data)) array_push($errorPreviewData, $data['invalidBarcode']);
                if (array_key_exists("invalidTitle", $data)) array_push($errorPreviewData, $data['invalidTitle']);
                if (array_key_exists("productType", $data)) array_push($errorPreviewData, $data['productType']);
            }


            $columnTitles = $request->requiredColumns;
            array_push($columnTitles, 'INVALID_DATA');
            $response = [
                'message' => Lang::get('lang.invalid_data_download_file_to_see_the_error'),
                'excelInvalidDatas' => $invalidDataCollection,
                'requiredColumns' => $columnTitles,
                'errorPreviewData' => $errorPreviewData
            ];
            return response()->json($response, 400);
        }
    }

    private function importFileValidation($productImportedData, $correctColumnNames)
    {
        //column key name must have the same as demo file columns name
        $data = $productImportedData[0];
        $inCorrectColumn[] = "";
        foreach ($correctColumnNames as $correctColumnName) {
            if (isset($data[$correctColumnName])) return $isValid = true;
            else {
                foreach ($correctColumnNames as $column) {
                    if (!isset($data[$column])) array_push($inCorrectColumn, $column);
                }
                return ['inCorrectColumn' => $inCorrectColumn];
            }
        }
    }

    private function importStandardProduct($productData, $index, $fillAble)
    {
        $errorData = [];
        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;

        $count = ProductVariant::alreadyExisted($productData['sku'], $productData['bar_code'], $productData['title']);
        if ($count == 0 && !empty($productData['title'])) {
            $data = $this->insertProductAttributeForImport($productData);
            $variantData = array(
                'sku' => $skuPrefix . $productData['sku'],
                'product_id' => $data['productId'],
                'variant_title' => 'default_variant',
                'attribute_values' => 'default_variant',
                'purchase_price' => $productData['purchase_price'],
                'selling_price' => $productData['selling_price'],
                'enabled' => 1,
                'bar_code' => $productData['bar_code'],
                're_order' => $productData['re_order']
            );
            ProductVariant::store($variantData);
            return true;
        } else {
            $errorData['index'] = $index;

            if ($productData['sku'] != null) {
                if (ProductVariant::ifExists('product_variants.sku', $productData['sku'])) $errorData['invalidSku'] = $productData['sku'];
            }
            if ($productData['bar_code'] != null) {
                if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) $errorData['invalidBarcode'] = $productData['bar_code'];
            }

            if (Product::ifExists('products.title', $productData['title'])) $errorData['invalidTitle'] = $productData['title'];
            foreach ($fillAble as $field) {
                if ($productData[$field] == null) $errorData['emptyData'] = $productData[$field];
            }
            return $errorData;
        }
    }

    private function importVariantProduct($productData, $index)
    {
        $attributes = explode(",", $productData['variant_title']);

        $createdBy = Auth::user()->id;
        foreach ($attributes as $attribute) {

            $checkAttributeExists = ProductAttribute::where('name', $attribute)->exists();
            if (!$checkAttributeExists) {
                ProductAttribute::insertData(['name' => $attribute, 'created_by' => $createdBy]);
            }
        }

        $checkVariant = Product::getAllDetails($productData);
        if ($checkVariant == 1) {
            $data = Product::getVariantId($productData);
            $productId = $data['id'];
        } else {
            $data = $this->insertProductAttributeForImport($productData);
            $productId = $data['productId'];
        }
        $errorData = [];
        $attributeName = explode(",", $productData['variant_title']);
        $attributeValue = explode(",", $productData['attribute_value']);

        $attributeData = [];
        array_push($attributeData, null);
        foreach ($attributeValue as $attribute) {
            array_push($attributeData, array(ucfirst($attribute)));
        }
        $variantAttributeInsertValue = $this->variantAttributeInsertValues($attributeData, $data['productId']);
        ProductAttributeValue::insertData($variantAttributeInsertValue);
        $variantAttributeInsertValue = [];
        $attributeItems = [];

        foreach ($attributeName as $item) {
            $id = ProductAttribute::getIdOfExisted('name', $item);
            if (isset($id)) {
                array_push($attributeItems, $id->id);
            }
        }
        foreach ($attributeItems as $item) {
            for ($i = 0; $i < sizeof($attributeName); $i++) {
                array_push($variantAttributeInsertValue, ['product_id' => $data['productId'], 'attribute_id' => $item, 'values' => $attributeValue[$i]]);
            }
        }

        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;

        $variantData = array(
            'sku' => $skuPrefix . $productData['sku'],
            'product_id' => $productId,
            'variant_title' => ucfirst($productData['attribute_value']),
            'attribute_values' => ucfirst($productData['attribute_value']),
            'variant_details' => $productData['variant_details'],
            'purchase_price' => $productData['purchase_price'],
            'selling_price' => $productData['selling_price'],
            'enabled' => 1,
            'bar_code' => $productData['bar_code'],
            're_order' => $productData['re_order']
        );

        $count = ProductVariant::alreadyExisted($productData['sku'], $productData['bar_code'], $productData['title']);

        if ($count == 0) {
            ProductVariant::store($variantData);
            return true;
        } else {

            $errorData['index'] = $index;
            if ($productData['sku'] != null) {
                if (ProductVariant::ifExists('sku', $productData['sku'])) {
                    $errorData['invalidSku'] = $productData['sku'];
                }
            }
            if ($productData['bar_code'] != null) {
                if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) {
                    $errorData['invalidBarcode'] = $productData['bar_code'];
                }
            }

            if ($productData['sku'] == null && $productData['bar_code'] == null) {
                return true;
            } else {
                return $errorData;
            }
        }
    }

    private function insertProductAttributeForImport($data)
    {
        $existedCategory = ProductCategory::getIdOfExisted('name', $data['category_id']);
        if (isset($existedCategory)) $categoryId = $existedCategory->id;
        else $categoryId = ProductCategory::getInsertedId(['name' => $data['category_id'], 'created_by' => $data['created_by']]);
        $data['category_id'] = $categoryId;

        $existedBrand = ProductBrand::getIdOfExisted('name', $data['brand_id']);
        if (isset($existedBrand)) $brandId = $existedBrand->id;
        else $brandId = ProductBrand::getInsertedId(['name' => $data['brand_id'], 'created_by' => $data['created_by']]);
        $data['brand_id'] = $brandId;

        $existedGroup = ProductGroup::getIdOfExisted('name', $data['group_id']);
        if (isset($existedGroup)) $groupId = $existedGroup->id;
        else $groupId = ProductGroup::getInsertedId(['name' => $data['group_id'], 'created_by' => $data['created_by']]);
        $data['group_id'] = $groupId;

        $existedUnit = ProductUnit::idOfExisted('*', ['name', 'short_name'], [$data['name'], $data['short_name']]);
        if (isset($existedUnit)) $unitId = $existedUnit->id;
        else $unitId = ProductUnit::getInsertedId(['name' => $data['name'], 'short_name' => $data['short_name'], 'created_by' => $data['created_by']]);
        $data['unit_id'] = $unitId;
        $data['taxable'] = 0;
        $data['tax_type'] = 'custom';
        unset($data['variant_title'], $data['purchase_price'], $data['selling_price'], $data['attribute_value'], $data['variant_details'], $data['sku'], $data['bar_code'], $data['re_order'], $data['name'], $data['short_name']);
        return ['productId' => Product::getInsertedId($data)];
    }

    public function importOpeningStock(Request $request)
    {
        $createdBy = Auth::user()->id;
        $check = 0;
        $invalidData = [];
        $errorPreviewData = [];
        $invalidDataCollection = [];

        // Validate with correct column name
        $isValid = $this->importFileValidation($request->importData, $request->requiredColumns);

        DB::beginTransaction();
        if ($isValid == "true") {
            $orderId = Order::getInsertedId([
                'date' => now(),
                'order_type' => 'adjustment',
                'type' => 'stock',
                'status' => 'done',
                'created_by' => $createdBy,
            ]);
            foreach ($request->importData as $index => $item) {

                $count = ProductVariant::alreadyExisted($item['SKU'], $item['BARCODE'], $item['TITLE']);

                if ($count == 1) {
                    $check++;
                    $data = ProductVariant::getProductIdForStock($item);

                    $productId = $data['productId'];
                    $variantId = $data['variantId'];
                    $purchasePrice = $data['purchase_price'];

                    OrderItems::insertData(
                        [
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'quantity' => $item['QUANTITY'],
                            'price' => $purchasePrice,
                            'order_id' => $orderId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                } else {
                    $errorData['index'] = $index;
                    if (!ProductVariant::ifExists('product_variants.sku', $item['SKU'])) $errorData['invalidSku'] = $item['SKU'];
                    if (!ProductVariant::ifExists('product_variants.bar_code', $item['BARCODE'])) $errorData['invalidBarcode'] = $item['BARCODE'];
                    if (!Product::ifExists('products.title', $item['TITLE'])) $errorData['invalidTitle'] = $item['TITLE'];
                    array_push($invalidData, $errorData);
                }
            }
        }
        if (sizeof($request->importData) == $check) {
            DB::commit();
            $response = [
                'message' => Lang::get('lang.opening_stock') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];
            return response()->json($response, 201);
        } else {
            DB::rollback();
            $showInvalidData = $request->importData;
            foreach ($showInvalidData as $index => $product) {
                foreach ($invalidData as $item) {
                    if ($item['index'] == $index) {
                        if (array_key_exists("invalidSku", $item) && array_key_exists("invalidBarcode", $item) && array_key_exists("invalidTitle", $item)) {
                            $product['INVALID_DATA'] = Lang::get('lang.non_existing_product');
                        } else {
                            $excelErrorData = [];
                            if (array_key_exists("invalidSku", $item)) array_push($excelErrorData, Lang::get('lang.sku') . ': ' . $item['invalidSku'] . ",");
                            if (array_key_exists("invalidBarcode", $item)) array_push($excelErrorData, Lang::get('lang.barcode') . ': ' . $item['invalidBarcode'] . ",");
                            if (array_key_exists("invalidTitle", $item)) array_push($excelErrorData, Lang::get('lang.title') . ': ' . $item['invalidTitle'] . ",");
                            array_push($excelErrorData, Lang::get('lang.does_not_match_with_any_product'));
                            $product['INVALID_DATA'] = implode(" ", $excelErrorData);
                        }
                    }
                }
                array_push($invalidDataCollection, $product);
            }
            foreach ($invalidData as $data) {
                if (array_key_exists("invalidSku", $data)) {
                    array_push($errorPreviewData, $data['invalidSku']);
                }
                if (array_key_exists("invalidBarcode", $data)) {
                    array_push($errorPreviewData, $data['invalidBarcode']);
                }
                if (array_key_exists("invalidTitle", $data)) {
                    array_push($errorPreviewData, $data['invalidTitle']);
                }
            }
            $columnTitles = $request->requiredColumns;
            array_push($columnTitles, 'INVALID_DATA');
            $response = [
                'message' => Lang::get('lang.invalid_data_download_file_to_see_the_error'),
                'excelInvalidDatas' => $invalidDataCollection,
                'requiredColumns' => $columnTitles,
                'errorPreviewData' => $errorPreviewData
            ];

            return response()->json($response, 400);
        }
    }

    public function getSupportingData()
    {
        $category = ProductCategory::index(['name as text', 'id as value']);
        $group = ProductGroup::index(['name as text', 'id as value']);
        $brand = ProductBrand::index(['name as text', 'id as value']);
        $variant = ProductVariant::index(['sku as sku', 'bar_code as bar_code', 'product_id as product_id', 'attribute_values as attribute_values']);
        return ['category' => $category, 'group' => $group, 'brand' => $brand, 'variant' => $variant];
    }
}
