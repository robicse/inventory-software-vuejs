<?php

namespace App\Models;

use DB;

class Product extends BaseModel

{
    protected $fillable = ['title', 'category_id', 'brand_id', 'unit_id', 'group_id', 'taxable', 'tax_type', 'tax_id', 'product_type', 'imageURL', 'created_by'];

    public static function groupId($id)
    {
        return Product::where('group_id', $id)->count();
    }

    public static function getAllData($request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $offset = $request->rowOffset;
        $searchValue = $request->searchValue;
        $columnSortedBy = $request->columnSortedBy;
        $filtersData = $request->filtersData;
        $requestType = $request->reqType;

        if ($columnName == 'group_name') $columnName = 'product_groups.name';
        else if ($columnName == 'brand_name') $columnName = 'product_brands.name';
        else if ($columnName == 'category_name') $columnName = 'product_categories.name';

        $products = Product::leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id');

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {

                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "group") {
                    $products->where('products.group_id', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "brand") {
                    $products->where('products.brand_id', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "category") {
                    $products->where('products.category_id', $singleFilter['value']);
                }
            }
        }
        if ($searchValue) {
            $products->select('products.*', 'product_groups.name as group_name', 'product_categories.name as category_name', 'product_brands.name as brand_name')
                ->where('products.title', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_categories.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_brands.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_groups.name', 'LIKE', '%' . $searchValue . '%');
        } else {
            $products->select('products.*', 'product_groups.name as group_name', 'product_categories.name as category_name', 'product_brands.name as brand_name');
        }

        $totalCount = $products->count();

        if (empty($requestType)) {
            $products = $products->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        } else {
            $products = $products->orderBy($columnName, $columnSortedBy)->get();
        }

        foreach ($products as $product){
            $variants = ProductVariant::getVariant($product->id);
            $product->variants = $variants;
        }
        return ['row' => $products, 'count' => $totalCount,];

    }


    public static function detailsById($id)
    {
        return Product::leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->leftJoin('product_units', 'product_units.id', '=', 'products.unit_id')
            ->leftJoin('users', 'users.id', '=', 'products.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'products.tax_id')
            ->select('products.id', 'products.title', 'products.taxable', 'products.tax_type', 'products.product_type', 'products.imageURL', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name', 'product_units.name as unit_name', 'taxes.name as tax_name', 'taxes.percentage', 'users.first_name', 'users.last_name')
            ->where('products.id', $id)
            ->first();
    }

    public static function availableStock()
    {
        return Product::join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('order_items', 'order_items.variant_id', '=', 'product_variants.id')
            ->groupBy('order_items.variant_id')
            ->select('products.id as prod_id', 'products.title as prod_title', 'product_variants.attribute_values as attributes', DB::raw('sum(quantity) as qtty'), 'product_variants.id as variant_id')
            ->get();
    }

    public static function categoryIdUsed($id)
    {
        return Product::where('category_id', $id)->count();
    }

    public static function usedProduct($id)
    {
        return Product::where('brand_id', $id)->count();
    }

    public static function getProducts($searchValue)
    {
        return Product::leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->select('products.id as productID', 'products.title', 'products.taxable', 'products.tax_type', 'products.tax_id', 'products.imageURL as productImage', 'products.branch_id', 'product_groups.name as group_name', 'product_categories.name as category_name', 'product_brands.name as brand_name', 'product_variants.sku')
            ->where('products.title', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_variants.sku', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_variants.bar_code', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_categories.name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_brands.name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_groups.name', 'LIKE', '%' . $searchValue . '%')
            ->groupBy('productID')
            ->get();

    }

    public static function insertProduct($productName, $category_id, $brand_id, $group_id, $unit_id, $product_type, $created_by)
    {
        return Product::insertGetId(
            [
                'title' => $productName,
                'category_id' => $category_id,
                'brand_id' => $brand_id,
                'group_id' => $group_id,
                'unit_id' => $unit_id,
                'product_type' => $product_type,
                'created_by' => $created_by
            ]
        );
    }

    public static function ifExists($field, $data)
    {
        if ($data == null) $data = '';

        return ProductVariant::select('product_variants.product_id as productId', 'product_variants.id as variantId', 'product_variants.purchase_price as purchase_price','product_variants.selling_price as selling_price', 'products.title as title')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->where($field, '=', $data)
            ->exists();
    }

    public static function getAllDetails($productData)
    {
        return Product::leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->select('products.id', 'products.title', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name')
            ->where('products.product_type', 'variant')
            ->where('product_categories.name', $productData['category_id'])
            ->where('product_brands.name', $productData['brand_id'])
            ->where('product_groups.name', $productData['group_id'])
            ->count();
    }

    public static function getVariantId($productData)
    {
        return Product::leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->select('products.id', 'products.title', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name')
            ->where('products.product_type', 'variant')
            ->where('product_categories.name', $productData['category_id'])
            ->where('product_brands.name', $productData['brand_id'])
            ->where('product_groups.name', $productData['group_id'])
            ->first();
    }
}