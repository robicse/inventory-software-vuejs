<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h5 v-if="id" class="m-0">{{ trans('lang.edit_product') }}</h5>
                        <h5 v-else class="m-0">{{ trans('lang.add_new_product') }}</h5>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <span aria-hidden="true"><i class="la la-close"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <pre-loader v-if="!hidePreLoader"></pre-loader>
        <div class="modal-body scroll-modal app-bg-color p-3" v-show="hidePreLoader">
            <div class="form-row mx-0 mb-3 bg-white rounded p-3">
                <div class="form-group col-md-12">
                    <label for="product">{{ trans('lang.name') }}</label>
                    <input id="product" v-validate="'required'" type="text" class="form-control" name="title"
                           v-model="product.pName" :class="{ 'is-invalid':submitted && errors.has('title') }">
                    <div class="heightError" v-if="submitted && errors.has('title')">
                        <small class="text-danger" v-show="errors.has('title')">{{ errors.first('title') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="product-category">{{ trans('lang.category') }}</label>
                    <select v-model="product.category" id="product-category" class="form-control">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="category in categoryList" :value="category.id">{{ category.name }}</option>
                    </select>
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="product-brand">{{ trans('lang.brand') }}</label>
                    <select v-model="product.brand" id="product-brand" class="form-control">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="brand in brandList" :value="brand.id">{{ brand.name }}</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-group">{{ trans('lang.group') }}</label>
                    <select v-model="product.group" id="product-group" class="form-control">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="group in groupList" :value="group.id">{{ group.name }}</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-unit">{{ trans('lang.unit') }}</label>
                    <select v-model="product.unit" id="product-unit" class="form-control">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="unit in unitList" :value="unit.id">{{ unit.name }}</option>
                    </select>
                </div>
                <div class="mb-2 col-md-6">
                    <label>{{ trans('lang.upload_product_image') }}</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="product-image" accept="image/*"
                               @change="productImage">
                        <label class="custom-file-label text-truncate" for="product-image">{{ trans('lang.image_only')
                            }}</label>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-tax">{{ trans('lang.vat') }}</label>
                    <select v-model="product.tax" v-validate="'required'" id="product-tax" name="tax"
                            class="form-control">
                        <option value="" disabled>{{ trans('lang.choose_one') }}</option>
                        <option value="no-tax">No Vat</option>
                        <!--<option value="default-tax">{{ trans('lang.default_tax') }}</option>-->
                        <option v-for="tax in taxList" :value="tax.id">{{ tax.name }}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('tax')">{{ errors.first('tax') }}</small>
                    </div>
                </div>
            </div>
            <div class="form-row mx-0 mb-3 bg-white rounded p-3" v-if="!id">
                <div class="col-md-12 mb-3">
                    <h5 class="mb-0">{{ trans('lang.chose_product_type') }}</h5>
                </div>
                <div class="col-md-12">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio"
                               name="productVariant"
                               class="custom-control-input"
                               id="standard-product"
                               checked="checked"
                               value="0"
                               v-model="product.variantType"
                               @change="selectStandardProduct()">
                        <label class="custom-control-label" for="standard-product">{{ trans('lang.standard_product')
                            }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio"
                               name="productVariant"
                               class="custom-control-input"
                               id="variant-product"
                               value="1"
                               v-model="product.variantType"
                               @change="checkVariant = true">
                        <label class="custom-control-label" for="variant-product">{{ trans('lang.variant_product')
                            }}</label>
                    </div>
                </div>
            </div>
            <div v-if="!checkVariant" class="form-row mx-0 mb-3 bg-white rounded p-3">
                <div class="form-group col-md-4" v-if="hideCommonInput">
                    <label>{{ trans('lang.receiving_price') }}</label>
                    <common-input v-validate="'required'"
                                  data-vv-as="receiving price"
                                  name="receivingPrice"
                                  id="'standard-product-receiving-price'"
                                  step="any"
                                  :inputValue="decimalFormat(product.receivingPrice)" @input="setReceivingValue"
                                  :class="{ 'is-invalid':submitted && errors.has('receivingPrice') }">
                    </common-input>
                    <div class="heightError" v-if="submitted && errors.has('receivingPrice')">
                        <small class="text-danger" v-show="errors.has('receivingPrice')">{{
                            errors.first('receivingPrice') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-4" v-if="hideCommonInput">
                    <label>{{ trans('lang.selling_price') }}</label>
                    <common-input v-if="hidePreLoader" v-validate="'required'"
                                  data-vv-as="selling price"
                                  name="sallingPrice"
                                  id="'standard-product-salling-price'"
                                  step="any"
                                  :inputValue="decimalFormat(product.sallingPrice)" @input="setSallingValue"
                                  :class="{ 'is-invalid':submitted && errors.has('sallingPrice') }">
                    </common-input>
                    <div class="heightError" v-if="submitted && errors.has('sallingPrice')">
                        <small class="text-danger" v-show="errors.has('sallingPrice')">{{ errors.first('sallingPrice')
                            }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="standard-product-sku">{{trans('lang.sku')}}</label>
                    <input id="standard-product-sku" type="text" step="any" name="sku" class="form-control"
                           v-model="standardProductSku">
                    <div class="heightError">
                        <small class="text-danger"
                               v-show="sku.includes(standardProductSku) && standardProductSku != null && standardProductSku !== '' && checkSubmit">
                            {{trans('lang.sku_already_exists') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="standard-product-barcode">{{trans('lang.barcode')}}</label>
                    <input id="standard-product-barcode" type="text" name="barcode" step="any" class="form-control"
                           v-model="standardProductBarcode">
                    <div class="heightError">
                        <small class="text-danger"
                               v-show="barcode.includes(standardProductBarcode) && standardProductBarcode != null && standardProductBarcode !== '' && checkSubmit">
                            {{trans('lang.barcode_already_exists') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-expireddate">Expired Date</label>
                    <input id="product-expireddate" type="text" step="any" class="form-control" v-model="product.expireddate" placeholder="YYYY-MM-DD">
                </div>
                <!--<div class="form-group col-md-4">
                    <label>{{trans('lang.re_order')}}</label>
                    <input id="product-re-order" type="hidden" step="any" class="form-control"
                           v-model="product.reorder">
                </div>-->
            </div>

            <div v-else id="addVariantSection" class="mb-3 bg-white rounded p-3">

                <div class="row">
                    <div class="col-5">
                        <h5>{{ trans('lang.add_product_variants') }}</h5>
                    </div>
                    <div class="col-7">
                        <div class="row no-gutters">
                            <div class="col">
                                <div class="text-right mr-2" v-if="!id">
                                    <a class="btn btn-primary app-color text-white"
                                       data-toggle="modal"
                                       data-target="#attributes-add-edit-modal"
                                       @click.prevent="openAttributeModal">
                                        <i class="la la-plus-circle"></i> {{ trans('lang.add_new_variant') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col">
                                <div v-if="!id">
                                    <select id="inputState" class="form-control" @change="addTempAttribute($event)">
                                        <option selected disabled>{{ trans('lang.add_another_variant') }}</option>
                                        <option v-for="productAttribute in allAttributes" :value="productAttribute.id">
                                            {{ productAttribute.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="loader" v-if="attributeLoader"></div>
                <div v-else v-for="(tempAttribute,index) in tempAttributeList">
                    <div class="row">
                        <div class="col-12">
                            <div class="variant-values">
                                <label>{{ capitalizeFirstLetter(tempAttribute.name) }}</label>
                                <div class="chips-container">
                                    <span class='chip' v-for="(chips,chipIndex) in chipArray[tempAttribute.id]">
                                        {{ chips }}
                                        <i v-if="!id" class='la la-close close'
                                           @click.prevent='deleteChip($event,tempAttribute.id,chipIndex)'></i>
                                    </span>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control"
                                           @keyup.enter="addChips($event,tempAttribute.id)"
                                           aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button"
                                                @click.prevent="addChips($event,tempAttribute.id)">
                                            <i class="la la-plus-circle text-info"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" v-if="!id"
                                                @click.prevent="removeTempAttribute(index,tempAttribute.id)">
                                            <i class="la la-trash-o text-danger"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded p-3" v-if="productVariant.length>0 && checkVariant">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <h5 class="m-0">{{ trans('lang.add_variant_details') }}</h5>
                    </div>
                </div>
                <!--For Edit variants-->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr class="border-0">
                            <th></th>
                            <th class="text-center">{{ trans('lang.variants') }}</th>
                            <th class="text-center">{{ trans('lang.receiving_price') }}</th>
                            <th class="text-center">{{ trans('lang.selling_price') }}</th>
                            <th class="text-center">{{ trans('lang.barcode') }}</th>
                            <th class="text-center">{{ trans('lang.sku') }}</th>
                            <th class="text-center">{{ trans('lang.re_order') }}</th>
                            <th class="text-center">{{ trans('lang.variant_image') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(elementName, index) in productVariant">

                            <!--Edit check box-->
                            <td class="border-0 add-product-padding">
                                <div class="custom-control custom-checkbox" style="top:-0.75rem">
                                    <input type="checkbox" class="custom-control-input" :id="'variant-available-'+index"
                                           v-model="pEnabled[index]">
                                    <label class="custom-control-label" :for="'variant-available-'+index"></label>
                                </div>
                            </td>

                            <!--Edit Variant combination-->
                            <td class="border-0 add-product-padding">
                                <input v-model="pVariant[index]" type="text" class="form-control"
                                       :disabled="!pEnabled[index]" style="width: 100%">
                            </td>

                            <!--Edit price of the variant-->
                            <td class="border-0 add-product-padding">
                                <common-input :id="'product_receiving_price'+index"
                                              v-validate="'required'"
                                              data-vv-as="receiving price"
                                              :name="'pReceivingPrice'+index "
                                              :disabled="!pEnabled[index]"
                                              @change="setPriceVariant($event,index)"
                                              step="any"
                                              :inputValue="decimalFormat(pReceivingPrice[index])" :index="index"
                                              @input="setReceivingValueVariant"
                                              v-if="hideInputField"

                                >
                                </common-input>
                                <div class="heightError text-nowrap">
                                    <small class="text-danger" v-show="errors.has('pReceivingPrice'+index)">
                                        {{ errors.first('pReceivingPrice'+index) }}
                                    </small>
                                </div>
                            </td>
                            <td class="border-0 add-product-padding">
                                <common-input :id="'product_selling_price'+index"
                                              v-validate="'required'"
                                              data-vv-as="selling price"
                                              :name="'pSellingPrice'+index "
                                              :disabled="!pEnabled[index]"
                                              @change="setPriceVariant($event,index)"
                                              step="any"
                                              :inputValue="decimalFormat(pSellingPrice[index])" :index="index"
                                              @input="setSellingValueVariant"
                                              v-if="hideInputField"
                                >
                                </common-input>
                                <div class="heightError text-nowrap">
                                    <small class="text-danger" v-show="errors.has('pSellingPrice'+index)">
                                        {{ errors.first('pSellingPrice'+index) }}
                                    </small>
                                </div>
                            </td>

                            <!--Edit barcode of the variant-->
                            <td class="border-0 add-product-padding">
                                <input :id="'product_barcode'+index"
                                       type="text"
                                       v-model="pBarcode[index]"
                                       step="any"
                                       class="form-control"
                                       :disabled="!pEnabled[index]">
                                <div class="heightError text-nowrap">
                                    <small class="text-danger"
                                           v-show="(pBarcode.filter(function(barcode){return barcode === pBarcode[index]}).length>1 || barcode.includes(pBarcode[index])) && pBarcode[index] != null && pBarcode[index] !== '' && checkSubmit">
                                        {{trans('lang.barcode_already_exists') }}
                                    </small>
                                </div>
                            </td>

                            <!--Edit sku of the variant-->
                            <td class="border-0 add-product-padding">
                                <input :id="'product_sku'+index" type="text"
                                       v-model="pSku[index]" step="any"
                                       class="form-control"
                                       :disabled="!pEnabled[index]">{{checkVariantSku[index]}}
                                <div class="heightError text-nowrap">
                                    <small class="text-danger"
                                           v-show="(pSku.filter(function(sku){return sku === pSku[index]}).length>1 || sku.includes(pSku[index])) && pSku[index] != null && pSku[index] !== '' && checkSubmit">
                                        {{trans('lang.sku_already_exists') }}
                                    </small>
                                </div>
                            </td>

                            <td class="border-0 add-product-padding">
                                <input :id="'product_reorder'+index"
                                       type="number"
                                       min="0"
                                       v-model="pReorder[index]"
                                       step="any"
                                       class="form-control"
                                       :disabled="!pEnabled[index]">
                            </td>
                            <td class="border-0 add-product-padding">
                                <div class="custom-file" style="padding-right: 100%">
                                    <input type="file"
                                           class="custom-file-input"
                                           :disabled="!pEnabled[index]"
                                           :id="'variant-image-'+index"
                                           accept="image/*"
                                           @change="variantImage($event, index, '#variant-image-'+index)">
                                    <label class="custom-file-label text-truncate" :for="'variant-image-'+index">
                                        {{ trans('lang.image_only') }}
                                    </label>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-row mt-3">
                <div class="col-md-12">
                    <button class="btn btn-primary app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save') }}
                    </button>
                    <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal" aria-label="Close"
                            @click.prevent="">
                        {{ trans('lang.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    let sourceURL = '/products/attribute/';
    export default {
        props: ['id', 'bus'],
        extends: axiosGetPost,
        data() {
            return {
                submitted: false,
                checkVariant: false,
                categoryList: [],
                groupList: [],
                unitList: [],
                brandList: [],
                branchList: [],
                allAttributes: [],
                attributeList: [],
                taxList: [],
                tempAttributeList: [],
                totalChipValue: [],
                product: [],
                standardProductSku: '',
                standardProductBarcode: '',
                pVariant: [],
                pPrice: [],
                pSellingPrice: [],
                pReceivingPrice: [],
                pSku: [],
                pBarcode: [],
                pReorder: [],
                sku: [],
                checkVariantSku: [],
                checkVariantBarcode: [],
                showSkuError: false,
                showBarcodeError: false,
                checkSubmit: false,
                barcode: [],
                reorder: [],
                pQuantity: [],
                pEnabled: [],
                productVariant: [],
                productVariantImages: [],
                chipArray: [],
                totalProductCount: 0,
                hidePreLoader: true,
                variantDetails: [],
                attributeLoader: false,
                productsInfo: {},
                editAttributeData: [],
                idCheck: '',
                sallingPrice: '',
                receivingPrice: '',
                productQuantity: '',
                productEnabled: '',
                productSku: '',
                productBarcode: '',
                isActiveModal: false,
                productReorder: '',
                defaultReorder: '',
                variantCountRow: 0,
                isActive: false,
                variantItemCount: 0,
                countMethodCalled: 0,
                variantCombination: [],
                duplicateSku: [],
                duplicateBarcode: [],
                hideCommonInput: false,
                productVariantIndex: '',
                hideInputField: true,
                modalOptions: {
                    modalID: '#attributes-add-edit-modal',
                    addLang: 'lang.add_new_attribute',
                    editLang: 'lang.edit_attribute',
                    getDataURL: sourceURL,
                    postDataWithIDURL: sourceURL,
                    postDataWithoutIDURL: sourceURL + 'store',
                    turnOffLoader: true,
                    closeModal: '#attributes-add-edit-modal',
                },
                modalID: '#product-add-edit-modal'
            }
        },
        watch: {
            id: function (newVal) {
                Object.assign(this.$data, this.$options.data.apply(this));
                if (newVal) {
                    this.getEditData();
                } else {
                    this.getAddData();
                }
            },


        },

        mounted() {

            //here this code carry data in one time
            let instance = this;
            if (instance.id) {
                instance.getEditData();
            } else {
                instance.getAddData();
            }

            instance.product.tax = 'no-tax';
            instance.bus.$on('saveStatus', this.saveStatus);
            instance.countVariant();
            instance.product.enable = true;
            instance.product.variantType = 0;

            this.$hub.$on('attributeAddEdit', function (id, name) {
                instance.addEditAction(id, name);
            });

            $('#product-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.$emit('disable');
            });

            this.modalCloseAction(this.modalOptions.modalID);
        },
        methods: {
            closeModal() {
                this.$emit('resetModal');
            },
            setReceivingValue(amount) {
                this.product.receivingPrice = amount;
            },
            setSallingValue(amount) {
                this.product.sallingPrice = amount;
            },
            setReceivingValueVariant(amount, index) {

                this.pReceivingPrice[index] = amount;
            },
            setSellingValueVariant(amount, index) {
                this.pSellingPrice[index] = amount;
            },
            saveStatus() {
                let instance = this;
                this.axiosGet('/products/product-variant-attribute',
                    function (response) {
                        instance.allAttributes = response.data;
                        $('#inputState').prop('selected', false).find('option:first').prop('selected', true);
                    },
                    function (error) {

                    }
                )
            },
            openAttributeModal() {
                this.isActiveModal = true;
                this.$emit('clickedSomething', this.isActiveModal);
            },

            resetImport() {
                this.isFileLoaded = false;
                this.hasError = false;
                this.isDisabled = false;
                this.$emit('resetModal');
            },

            getAddData() {
                let instance = this;
                instance.setPreLoader(false);
                instance.attributeLoader = true;
                this.axiosGet('/products/attribute',
                    function (response) {
                        instance.barcode = response.data.getAllBarcode.allBarcode;
                        instance.sku = response.data.getAllSku.allSku;
                        instance.defaultReorder = response.data.productSupportingData.defaultReorder;
                        instance.product.reorder = instance.defaultReorder;
                        instance.allAttributes = response.data.productSupportingData.attributes;
                        instance.supportingData(response.data.productSupportingData);
                        instance.attributeList = response.data.productAttribute;
                        instance.tempAttributeList = instance.attributeList;
                        instance.attributeLoader = false;
                        instance.hideCommonInput = true;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                        instance.attributeLoader = false;
                    },
                );
            },
            getEditData() {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet('/products/edit-product/' + instance.id,
                    function (response) {
                        console.log(response);
                        instance.variantItemCount = response.data.variantDetails.length;
                        let result = _.clone(response.data);
                        let allAttributesProduct = _.clone(response.data.AllAttributesProduct);
                        instance.productsInfo = result;
                        instance.barcode = response.data.getAllBarcode.allBarcode;
                        instance.sku = response.data.getAllSku.allSku;
                        instance.product.pName = instance.productsInfo.productDetails.title;
                        instance.product.category = instance.productsInfo.productDetails.category_id;
                        instance.product.brand = instance.productsInfo.productDetails.brand_id;
                        instance.product.group = instance.productsInfo.productDetails.group_id;
                        instance.product.unit = response.data.productDetails.unit_id;
                        instance.product.sku = response.data.variantDetails.sku;
                        instance.product.barcode = response.data.variantDetails.bar_code;
                        instance.product.expireddate = response.data.variantDetails.expireddate;
                        instance.defaultReorder = response.data.defaultReorder;
                        if (instance.productsInfo.productDetails.taxable == 0) {
                            instance.product.tax = 'no-tax';
                        } else {
                            if (instance.productsInfo.productDetails.tax_type == 'default') {
                                instance.product.tax = 'default-tax';
                            } else {
                                instance.product.tax = instance.productsInfo.productDetails.tax_id;
                            }
                        }
                        if (instance.productsInfo.productDetails.product_type == "standard") {
                            instance.product.variantType = 0;
                            instance.product.receivingPrice = instance.productsInfo.variantDetails[0].purchase_price;
                            instance.product.sallingPrice = instance.productsInfo.variantDetails[0].selling_price;
                            instance.standardProductSku = response.data.variantDetails[0].sku;
                            instance.standardProductBarcode = response.data.variantDetails[0].bar_code;
                            instance.product.reorder = response.data.variantDetails[0].re_order;
                            instance.product.expireddate = response.data.variantDetails[0].expireddate;
                        } else {
                            instance.product.variantType = 1;
                            instance.checkVariant = true;
                            let deleteIndex = [];

                            allAttributesProduct.forEach(function (value, index) {
                                let arrribute_id = value.id;
                                if (instance.productsInfo.variantData[arrribute_id] === undefined) {
                                    deleteIndex.push(index);
                                } else {
                                    instance.chipArray[arrribute_id] = instance.productsInfo.variantData[arrribute_id];
                                }
                            });

                            let pulled = _.pullAt(allAttributesProduct, deleteIndex);
                            instance.attributeList = allAttributesProduct;
                            instance.tempAttributeList = instance.attributeList;
                            instance.countVariant();
                            instance.productsInfo.variantDetails.forEach(function (productPrice, key) {
                                instance.pSellingPrice[key] = productPrice.selling_price;
                                instance.pReceivingPrice[key] = productPrice.purchase_price;

                                if (productPrice.enabled == 1) {
                                    instance.pEnabled[key] = true;
                                } else {
                                    instance.pEnabled[key] = false;
                                }
                            })
                            instance.productsInfo.variantDetails.forEach(function (productSku, key) {
                                instance.pSku[key] = productSku.sku;
                                if (productSku.enabled == 1) {
                                    instance.pEnabled[key] = true;
                                } else {
                                    instance.pEnabled[key] = false;
                                }
                            })
                            instance.productsInfo.variantDetails.forEach(function (productBarcode, key) {
                                instance.pBarcode[key] = productBarcode.bar_code;
                                if (productBarcode.enabled == 1) {
                                    instance.pEnabled[key] = true;
                                } else {
                                    instance.pEnabled[key] = false;
                                }
                            })
                            instance.productsInfo.variantDetails.forEach(function (productReorder, key) {
                                instance.pReorder[key] = productReorder.re_order;
                                if (productReorder.enabled == 1) {
                                    instance.pEnabled[key] = true;
                                } else {
                                    instance.pEnabled[key] = false;
                                }
                            });
                            instance.productsInfo.variantDetails.forEach(function (productBarcode, key) {
                                instance.pBarcode[key] = productBarcode.bar_code;
                                if (productBarcode.enabled == 1) {
                                    instance.pEnabled[key] = true;
                                } else {
                                    instance.pEnabled[key] = false;
                                }
                            })
                        }
                        instance.supportingData(response.data.productSupportingData);
                        instance.hideCommonInput = true;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            countVariant() {
                this.hideInputField = false;
                this.productVariant = [];
                this.pVariant = [];
                this.productVariantImages = [];
                let tempProductVariant = [],
                    tempVariantDetails = [],
                    chipArrayLoopingTime = 0,
                    count = 0,
                    combinationIndex = 0,
                    newVariantCombination = 0,
                    a = [];
                for (let i = 0; i < this.chipArray.length; i++) {
                    if (this.chipArray[i]) {
                        chipArrayLoopingTime++;

                        if (tempProductVariant.length == 0) {
                            tempProductVariant = this.chipArray[i];
                        } else {
                            if (chipArrayLoopingTime > 2) {
                                tempProductVariant = tempVariantDetails;
                                tempVariantDetails = [];
                                count = 0;
                            }

                            for (let t = 0; t < tempProductVariant.length; t++) {
                                for (let j = 0; j < this.chipArray[i].length; j++) {
                                    tempVariantDetails[count] = tempProductVariant[t] + ',' + this.chipArray[i][j];
                                    this.pReorder[j] = this.defaultReorder;
                                    count++;
                                }
                            }
                        }
                    }
                }
                let newAddedChips = count - this.variantItemCount;
                if (newAddedChips > 0) {
                    for (let i = 0; i < newAddedChips; i++) {
                        if (i < this.pSku.length && this.pSku[i] == null) {
                            this.pSku.push(null);
                        }
                        if (i < this.pBarcode.length && this.pBarcode[i] == null) {
                            this.pBarcode.push(null);
                        }
                    }
                }
                if (tempVariantDetails.length > 0) {
                    this.productVariant = tempVariantDetails;
                } else {
                    this.productVariant = tempProductVariant;
                }
                //add new variant chips while editing
                if (this.productVariant.length > 0) {

                    this.variantCombination.push(this.productVariant);

                    let oldVariantCombination = [];
                    _.forIn(this.variantCombination[0], function (oldVariant) {
                        oldVariantCombination.push(oldVariant);
                    });
                    let indexOFExisting = [];
                    this.productVariant.forEach(function (newVariant, indexOfNewVariant) {
                        oldVariantCombination.forEach(function (oldVariant, indexOfoldVariant) {
                            if (newVariant == oldVariant) {
                                indexOFExisting.push(indexOfNewVariant);
                            }
                        });
                    });
                    let tempReceivingPrice = this.pReceivingPrice.filter(value => value != null);
                    let tempSellingPrice = this.pSellingPrice.filter(value => value != null);

                    let tempBarcode = this.pBarcode;
                    let tempSku = this.pSku;
                    for (let i = 0; i < this.productVariant.length; i++) {
                        this.pVariant[i] = this.productVariant[i];
                        this.pEnabled[i] = true;
                        this.pReorder[i] = this.defaultReorder;
                        if (!indexOFExisting.includes(i)) {
                            tempReceivingPrice.splice(i, 0, null);
                            tempSellingPrice.splice(i, 0, null);
                            tempBarcode.splice(i, 0, null);
                            tempSku.splice(i, 0, null);
                        }
                    }
                    // Final value which in v-model of element
                    this.pReceivingPrice = tempReceivingPrice;
                    this.pSellingPrice = tempSellingPrice;
                    this.pBarcode = tempBarcode;
                    this.pSku = tempSku;
                    let instance = this;
                    setTimeout(function () {
                        instance.hideInputField = true;
                    });
                }
            },
            save() {
                this.submitted = true;
                let instance = this;
                instance.checkSubmit = true;
                instance.showSkuError = false;
                instance.showBarcodeError = false;
                if (instance.checkVariant) {
                    instance.pSku.forEach(function (productSku) {
                        if ((instance.pSku.filter(function (sku) {
                            return sku === productSku
                        }).length > 1 || instance.sku.includes(productSku)) && productSku != null && productSku !== '') instance.showSkuError = true;
                    });

                    instance.pBarcode.forEach(function (productBarcode) {
                        if ((instance.pBarcode.filter(function (barcode) {
                            return barcode === productBarcode
                        }).length > 1 || instance.barcode.includes(productBarcode)) && productBarcode != null && productBarcode !== '') instance.showBarcodeError = true;
                    });

                } else {
                    instance.showSkuError = instance.sku.includes(instance.standardProductSku);
                    instance.showBarcodeError = instance.barcode.includes(instance.standardProductBarcode);
                }

                this.$validator.validateAll().then((result) => {
                    if (result && !instance.showSkuError && !instance.showBarcodeError) {
                        /*console.log(instance);*/
                        if (instance.checkVariant) {
                            instance.sallingPrice = instance.pSellingPrice;
                            instance.receivingPrice = instance.pReceivingPrice;
                            instance.productQuantity = instance.pQuantity;
                            instance.productEnabled = instance.pEnabled;
                            instance.productSku = instance.pSku;
                            instance.productBarcode = instance.pBarcode;
                            instance.productReorder = instance.pReorder;
                        } else {
                            instance.sallingPrice = instance.product.sallingPrice;
                            instance.receivingPrice = instance.product.receivingPrice;
                            instance.productQuantity = instance.product.quantity;
                            instance.productEnabled = instance.product.enable = true;
                            instance.productSku = instance.standardProductSku;
                            instance.productBarcode = instance.standardProductBarcode;
                            instance.productReorder = instance.product.reorder;
                        }
                        if (this.id) {
                            console.log(instance.product);
                            this.postDataMethod('/products/edit/' + instance.id, {
                                name: instance.product.pName,
                                taxID: instance.product.tax,
                                category: instance.product.category,
                                brand: instance.product.brand,
                                group: instance.product.group,
                                unit: instance.product.unit,
                                expireddate: instance.product.expireddate,
                                branch: instance.product.branch,
                                type: instance.product.variantType,
                                variant: instance.pVariant,
                                variantImage: instance.productVariantImages,
                                sallingPrice: instance.sallingPrice,
                                receivingPrice: instance.receivingPrice,
                                sku: instance.productSku,
                                barcode: instance.productBarcode,
                                reorder: instance.productReorder,
                                quantity: instance.productQuantity,
                                enabled: instance.productEnabled,
                                variantDetails: instance.productVariant,
                                image: instance.product.image,
                                chipValues: instance.chipArray,
                            });
                        } else {
                            /*console.log(instance.product);*/
                            this.postDataMethod('/products/store', {
                                name: instance.product.pName,
                                taxID: instance.product.tax,
                                category: instance.product.category,
                                brand: instance.product.brand,
                                group: instance.product.group,
                                unit: instance.product.unit,
                                expireddate: instance.product.expireddate,
                                branch: instance.product.branch,
                                type: instance.product.variantType,
                                variant: instance.pVariant,
                                variantImage: instance.productVariantImages,
                                sallingPrice: instance.sallingPrice,
                                receivingPrice: instance.receivingPrice,
                                sku: instance.productSku,
                                barcode: instance.productBarcode,
                                reorder: instance.productReorder,
                                quantity: instance.productQuantity,
                                enabled: instance.productEnabled,
                                variantDetails: instance.productVariant,
                                image: instance.product.image,
                                chipValues: instance.chipArray,
                            });
                        }
                    }
                });
            },
            productImage(event) {
                let fileName = event.target.files[0].name;
                $('#product-image').next('.custom-file-label').html(fileName);
                let input = event.target;
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        this.product.image = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                } else {
                    this.product.image = '';
                }
            },
            variantImage(event, index, classID) {
                let input = event.target;
                if (input.files && input.files[0]) {
                    let fileName = event.target.files[0].name;
                    $(classID).next('.custom-file-label').html(fileName);

                    let reader = new FileReader();
                    reader.onload = (e) => {
                        this.productVariantImages[index] = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                } else {
                    this.productVariantImages[index] = '';
                }
            },
            postDataThenFunctionality(response) {
                this.closeModal();
                let instance = this;
                $('#product-add-edit-modal').modal('hide');
                instance.$hub.$emit('reloadDataTable');
            },
            postDataCatchFunctionality(error) {
            },
            supportingData(data) {
                let instance = this;
                instance.brandList = data.brands;
                instance.categoryList = data.categories;
                instance.groupList = data.groups;
                instance.unitList = data.units;
                instance.taxList = data.taxes;
                instance.branchList = data.branches;
                instance.allAttributes = data.attributes;
                if (instance.branchList.length == 1) {
                    instance.product.branch = instance.branchList[0].id;
                }
            },
            setActiveAttributeModal(value) {
                this.$emit('setActiveAttributeModal', value);
            },
            setPreLoader(value) {
                this.hidePreLoader = value;
            },
            onImageUpload(e) {
                let files = e.target.files || e.dataTransfer.files;
                if (!files.length)
                    return;
                this.createImage(files[0]);
            },
            createImage(file) {
                let reader = new FileReader();
                let instance = this;
                reader.onload = (e) => {
                    instance.product.image = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
            arrayRemove(array, element) {
                let index = array.indexOf(element);
                if (index !== -1) {
                    array.splice(index, 1);
                }
            },
            addChips(event, tempAttributeID) {
                let value = $(event.target).closest('.variant-values').find('input[type=text]').val();
                if (value != '') {
                    if (!this.chipArray[tempAttributeID]) {
                        this.chipArray[tempAttributeID] = [];
                    }
                    this.chipArray[tempAttributeID].push(this.capitalizeFirstLetter(value));
                    $(event.target).closest('.variant-values').find('input[type=text]').val(null);
                    let temp = this.tempAttributeList;
                    this.tempAttributeList = [];
                    this.tempAttributeList = temp;
                }
                this.countVariant();
            },
            deleteChip(event, tempAttributeID, index) {
                this.chipArray[tempAttributeID].splice(index, 1);
                if (this.chipArray[tempAttributeID].length == 0) {
                    this.chipArray[tempAttributeID] = null;
                }
                let temp = this.tempAttributeList;
                this.tempAttributeList = [];
                this.tempAttributeList = temp;
                this.countVariant();
            },
            addTempAttribute(event) {
                let index = event.target.value,
                    instance = this, attribute;
                instance.allAttributes.forEach(function (element) {
                    if (element.id == index) {
                        attribute = element;
                    }
                });

                if (_.find(this.tempAttributeList, {'id': attribute.id}) === undefined) {
                    instance.tempAttributeList.push(attribute);
                }
            },
            removeTempAttribute(index, tempAttributeID) {
                this.tempAttributeList.splice(index, 1);
                _.unset(this.chipArray, tempAttributeID);
                this.countVariant();
            },
            selectStandardProduct() {
                this.tempAttributeList = [];
                this.tempAttributeList = this.attributeList;
                this.checkVariant = false;
            },
            setPriceVariant(event, index) {

                if (index == 0) {
                    let tempPrice = [];
                    if (event.target.name == 'pReceivingPrice' + index) {
                        tempPrice = this.pReceivingPrice;
                    } else if (event.target.name == "pSellingPrice" + index) {
                        tempPrice = this.pSellingPrice;
                    }

                    for (let i = 1; i < tempPrice.length; i++) {

                        if (tempPrice[i] == null || tempPrice[i] == '') {
                            tempPrice[i] = parseInt(event.target.value);
                        }
                    }
                    if (event.target.name == 'pReceivingPrice' + index) {
                        this.pReceivingPrice = [];
                        this.pReceivingPrice = tempPrice;
                    } else if (event.target.name == "pSellingPrice" + index) {
                        this.pSellingPrice = [];
                        this.pSellingPrice = tempPrice;
                    }

                }

            },
            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
            },
        },
    }
</script>
