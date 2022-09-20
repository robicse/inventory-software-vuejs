<template>
    <div>
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <!--Export Button-->
            <div class="main-layout-card-header-with-button">
                <div class="main-layout-card-content-wrapper">
                    <div class="main-layout-card-header-contents">
                        <!--{{trans('lang')}}-->
                        <h5 class="m-0">{{ trans('lang.inventories') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                       <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                             :isActiveText="isActiveText" buttonText="export"
                                             v-on:submit="exportStatus"></common-submit-button>
                    </div>
                </div>
            </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content" :options="tableOptions" :exportData="exportToVue" :exportFileName="trans('lang.inventory')" @resetStatus="resetExportValue"></datatable-component>
        </span>

        <!-- Edit Modal -->
        <div class="modal fade" id="inventory-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <inventory-stock-in
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :modalID="modalID"
                        :modalTitle="trans('lang.updates')"
                        @updateItemsToStockIn = "updateItemsToStockIn">
                </inventory-stock-in>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                exportToVue:false,
                isActive:false,
                isActiveAttributeModal:false,
                selectedItemId: '',
                tableOptions: {},
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                modalID: '#inventory-edit-modal', // receive sale edit
                hasData: value => {
                    return !_.isEmpty(value) ? true : false
                },
            }
        },
        created(){
            this.getInventoryFilterAttributes();
        },

        mounted(){

            let instance = this;

            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e)
            {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });

            /*Receive Sale Edir*/
            this.$hub.$on('viewInventoryEdit', function (rowdata) {
                console.log(rowdata);
                instance.addEditAction(rowdata);
            });
        },

        methods: {

            getActiveAttributeModal(isActive)
            {
                this.isActiveAttributeModal = isActive;
            },

            getInventoryFilterAttributes()
            {
                let instance = this;
                instance.axiosGet('/inventory-reports-filter/',
                    function(response)
                    {
                        if(response.data){

                            let branchName = [],
                                brandName = [],
                                categoryName = [],
                                groupName = [];

                            //Appending static value(All) with dynamic Filter options from db
                            if(response.data.branchName) branchName     = [{text: instance.trans('lang.all'), value: 'all', selected: true}, ...response.data.branchName];
                            if(response.data.brandName) brandName       = [{text: instance.trans('lang.all'), value: 'all', selected: true}, ...response.data.brandName];
                            if(response.data.categoryName) categoryName = [{text: instance.trans('lang.all'), value: 'all', selected: true}, ...response.data.categoryName];
                            if(response.data.groupName) groupName       = [{text: instance.trans('lang.all'),value: 'all', selected: true}, ...response.data.groupName];

                            instance.tableOptions = {

                                tableName: 'cash_register_logs',
                                columns: [
                                    {title: 'lang.inventory_id',    key: 'id',              type: 'text', sortable: true},
                                    {title: 'lang.sku',             key: 'sku',             type: 'text', sortable: true},
                                    {title: 'lang.item_name',       key: 'porductTitle',    type: 'text', sortable: true},
                                    {title: 'lang.in_stock',       key: 'in_stock',       type: 'text', sortable: true},
                                    {title: 'lang.out_of_stock',       key: 'out_of_stock',       type: 'text', sortable: true},
                                    {title: 'lang.available_stock',       key: 'available_stock',       type: 'text', sortable: true},
                                    {title: 'lang.variant_name',    key: 'variantTitle',    type: 'text', sortable: true},
                                    {title: 'lang.category_name',   key: 'categoryTitle',   type: 'text', sortable: true},
                                    {title: 'lang.group_name',      key: 'groupTitle',      type: 'text', sortable: true},
                                    {title: 'lang.brand_name',      key: 'brandTitle',      type: 'text', sortable: true},
                                    {title: 'lang.purchase_price',      key: 'purchase_price',       type: 'text', sortable: true},
                                    {title: 'lang.selling_price',      key: 'selling_price',       type: 'text', sortable: true},
                                    {
                                        title: 'lang.action',
                                        type: 'component',
                                        componentName: 'inventory-action-component'
                                    }
                                ],
                                source: '/inventory-reports',
                                search: true,
                                sortedBy:'id',
                                sortedType:'DESC',
                                formatting : ['purchase_price', 'selling_price'],
                                right_align: ['purchase_price', 'selling_price', 'inventory'],
                                filters: [
                                    //dropdown filter for inventory report (dynamic value from db)
                                    {title: 'lang.branch',      key: 'branchName',      type: 'dropdown', languageType: "raw", options: branchName},
                                    {title: 'lang.brand',       key: 'brandName',       type: 'dropdown', languageType: "raw", options: brandName},
                                    {title: 'lang.category',    key: 'categoryName',    type: 'dropdown', languageType: "raw", options: categoryName},
                                    {title: 'lang.group',       key: 'groupName',       type: 'dropdown', languageType: "raw", options: groupName},
                                    {title: 'lang.re_order',   key: 'type', type: 'dropdown', options: [
                                            {text: 'lang.all',              value: 'all',       selected: true},
                                            {text: 'lang.yes',        value:'yes' },
                                            {text: 'lang.no',     value:'no'},
                                        ]},
                                ]
                            }
                        }

                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
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
            updateItemsToStockIn(updateItemsToStockIn){
                let instance = this;
                instance.hideSalesReturnsPreLoader = false;
                updateItemsToStockIn.paymentType = 'credit';

                instance.axiosGETorPOST(
                    {
                        url: '/save-due-amount',
                        postData: {updateItemsToStockIn},
                    },
                    (success, responseData) => {

                        if (success) //response after then function
                        {
                            instance.hideSalesReturnsPreLoader = true;
                            instance.showSuccessAlert(responseData.message);

                            $(`${this.modalID}`).modal('hide');
                            instance.$hub.$emit('reloadDataTable');

                        } else {
                            instance.hideSalesReturnsPreLoader = true;
                            $(`${this.modalID}`).modal('hide');
                        }
                    }
                );
            },
        }
    }

</script>
