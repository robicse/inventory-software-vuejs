<template>
    <div>
        <span v-if="!hidePreLoader">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <div class="main-layout-card-header-with-button">
                <div class="main-layout-card-content-wrapper">
                    <div class="main-layout-card-header-contents">
                        <h5 class="m-0">{{ trans('lang.products') }}</h5>
                    </div>
                    <div v-if="permission !== 'read_only'" class="main-layout-card-header-contents text-right d-flex justify-content-end">
                        <div class="p-1">
                            <button class="btn btn-primary app-color"
                                    @click.prevent="addEditAction(''),openProductModal()">
                                {{ trans('lang.add') }}
                            </button>
                        </div>
                         <div class="p-1">
                             <common-submit-button :buttonLoader="buttonLoader"
                                                   :isDisabled="isDisabled"
                                                   buttonText="export"
                                                   v-on:submit="exportStatus">
                             </common-submit-button>
                        </div>
                        <div class="p-1">
                            <button type="button"
                                    class="btn btn-primary app-color dropdown-toggle"
                                    data-toggle="dropdown">
                                    {{ trans('lang.action') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item drop-down-list"
                                   data-toggle="modal"
                                   @click="openModal('product')">
                                    {{ trans('lang.import_product') }}
                                </a>
                                <a class="dropdown-item drop-down-list"
                                   data-toggle="modal"
                                   @click="openModal('stock')">
                                    {{ trans('lang.import_opening_stock') }}
                                </a>
                                <span v-if="isPrintBarcode">
                                   <a class="dropdown-item drop-down-list"
                                      data-toggle="modal"
                                      @click.prevent="openBarcodeModal()">
                                         {{ trans('lang.print_barcode' )}}
                                   </a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 exportFileName="products"
                                 @resetStatus="resetExportValue"
                                 @printData="printData"
                                 @printBarcode="printBarcode"
                                 :tab_name="tabName"
                                 :route_name="routeName"
            >
            </datatable-component>
            <!-- Modal product -->
            <div class="modal fade" id="product-add-edit-modal" :style="[checkStatus ? { 'z-index': 1030 } : {}]"
                 tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                    <product-add-edit-modal class="modal-content" v-if="isActiveProduct" @resetModal="resetModal"
                                            @setActiveAttributeModal="getActiveAttributeModal"
                                            @clickedSomething="handleClickInParent"
                                            @disable="disableProductModal"
                                            :id="selectedItemId" :variantSaveStatus="variantSaveStatus"
                                            :bus="bus"></product-add-edit-modal>
                </div>
            </div>
            <!-- Modal variant-->
            <div class="modal fade" id="attributes-add-edit-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <variant-add-edit-common-modal :id="selectedItemId"
                                                       :modalID="modalID"
                                                       @resetModal="resetModal"
                                                       @resetProductModal="resetProductModal"
                                                       :modalOptions="modalOptions">
                        </variant-add-edit-common-modal>
                    </div>
                </div>
            </div>
            <!--Print Barcode Modal-->
            <barcode-modal v-if="isProductBarcodeModalActive"
                           @resetModal="resetModal"
                           @resetImport="resetImport"
                           :barcodeData="barcodeData"
                           :variantDatas='variantData'>
            </barcode-modal>
            <!-- Modal for import-->
            <import-modal v-if="isImportModalActive"
                          @resetModal="resetModal"
                          :importOptions="importOptions">
            </import-modal>
            <!-- Delete Modal -->
            <confirmation-modal id="confirm-delete"
                                :message="'brand_deleted_permanently'"
                                :firstButtonName="'yes'"
                                :secondButtonName="'no'"
                                @confirmationModalButtonAction="confirmationModalButtonAction">
            </confirmation-modal>
        </span>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    import Vue from 'vue';
    let sourceURL = '/products/attribute';
    export default {
        props: ['permission'],
        extends: axiosGetPost,
        data() {
            return {
                barcodeData: '',
                variantData: [],
                buttonLoader: false,
                isDisabled: false,
                exportToVue: false,
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                isActiveProduct: false,
                deleteID: '',
                hidePreLoader: false,
                isImportModalActive: false,
                isProductBarcodeModalActive: false,
                isPrintBarcode : false,
                variantSaveStatus: false,
                importOptions: {},
                tableOptions: {},
                checkStatus: false,
                tabName : 'products',
                routeName : 'products',
                modalOptions: {
                    modalID: '#attributes-add-edit-modal',
                    addLang: 'lang.add_new_attribute',
                    editLang: 'lang.edit_attribute',
                    getDataURL: sourceURL,
                    postDataWithIDURL: sourceURL,
                    postDataWithoutIDURL: sourceURL + '/store',
                    turnOffLoader: true,
                },
                modalID: '#product-add-edit-modal',
                bus: new Vue(),
            }
        },
        mounted() {
            let instance = this;
            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal',function (e){
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });

            this.$hub.$on('productEdit', function (id) {
                instance.isActiveProduct = true;
                instance.addEditAction(id);
             });
         },
        created() {
            this.getData();
        },
        methods: {

            getAddData(val){

            },

            handleClickInParent: function (value) {
                this.checkStatus = value;
            },
            disableProductModal() {
                this.isActiveProduct = false;
            },
            openProductModal() {
                this.isActiveProduct = true;
                $('#product-add-edit-modal').modal('show');
            },
            openModal(data) {
                this.isImportModalActive = true;
                setTimeout(function () {
                    $('#import-modal').modal('show');
                });
                if (data == 'stock') {
                    this.importOptions = {
                        title: 'lang.opening_stock',
                        routeToImport: '/products/import-stock',
                        requiredColumns: ['TITLE', 'SKU', 'BARCODE', 'QUANTITY'],
                        downloadSample: this.publicPath + "/sample/opening_stock.xlsx",
                    }
                } else {
                    this.importOptions = {
                        title: 'lang.product',
                        routeToImport: '/products/import',
                        requiredColumns: ["NAME", "CATEGORY", "BRAND", "GROUP", "UNIT", "UNIT_SHORT_NAME", "PRODUCT_TYPE", "VARIANT_NAME", "VARIANT_VALUE", "VARIANT_DETAIL", "SKU", "BARCODE", "RE-ORDER", "PURCHASE-PRICE", "SELLING-PRICE"],
                        downloadSample: this.publicPath + "/sample/product.xlsx",
                        fill_able: ["title"],
                    }
                }
            },
            openBarcodeModal() {
                this.isProductBarcodeModalActive = true;
                setTimeout(function () {
                    $('#barcode-modal').modal('show');
                });
                this.$emit('checking', 'this.isProductBarcodeModalActive');
            },
            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
            },
            confirmationModalButtonAction() {
                this.deleteDataMethod('/products/delete/' + this.deleteID, this.deleteIndex);
            },
            resetProductModal(value,save) {
                $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
                    this.isActiveAttributeModal = false;
                    $('body').addClass('modal-open');
                });
                this.checkStatus = value;

                if (save) {
                    this.bus.$emit('saveStatus');
                }
            },
            resetModal() {
                this.isProductBarcodeModalActive = false;
                this.isImportModalActive = false;
                this.selectedItemId = "";
                this.isActive = false;
            },
            resetImport(){
                this.isProductBarcodeModalActive = false;
            },
            getData() {
                let instance = this;
                instance.axiosGet('/products/supporting-data',
                    function (response) {
                        instance.variantData = response.data.variant;
                        let category = [{text: 'All', value: 'all', selected: true}, ...response.data.category],
                            group = [{text: 'All', value: 'all', selected: true}, ...response.data.group],
                            brand = [{text: 'All', value: 'all', selected: true}, ...response.data.brand];
                        instance.tableOptions = {
                            tableName: 'products',
                            columns: [
                                {
                                    title: 'lang.title',
                                    key: 'title',
                                    type: 'clickable_link',
                                    source: '/products/details',
                                    uniquefield: 'id',
                                    sortable: true
                                },
                                {title: 'lang.group', key: 'group_name', type: 'text', sortable: true},
                                {title: 'lang.brand', key: 'brand_name', type: 'text', sortable: true},
                                {title: 'lang.category', key: 'category_name', type: 'text', sortable: true},
                                (instance.permission !== 'read_only' ? {
                                    title: 'lang.action',
                                    type: 'component',
                                    key: 'action',
                                    componentName: 'product-action-component'
                                } : {})

                            ],
                            source: '/products/products',
                            search: true,
                            right_align: 'action',
                            filters: [
                                {
                                    title: 'lang.group',
                                    key: 'group',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: group
                                },
                                {
                                    title: 'lang.brand',
                                    key: 'brand',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: brand
                                },
                                {
                                    title: 'lang.category',
                                    key: 'category',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: category
                                },
                            ]
                        };
                        instance.hidePreLoader = true;
                    },
                    function (error) {
                        instance.hidePreLoader = true;
                    }
                );
            },
            exportStatus() {
                this.exportToVue = true;
                this.buttonLoader = true;
                this.isDisabled = true;
            },
            resetExportValue(value) {
                this.exportToVue = value;
                this.buttonLoader = false;
                this.isDisabled = false;
            },
            printData(value) {
                this.barcodeData = value;
            },
            printBarcode(){
                this.isPrintBarcode = true
            }
        }
    }
</script>