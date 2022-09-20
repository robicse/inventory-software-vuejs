<template>
    <div>
        <!--Export Button-->
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.receives') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                          :isActiveText="isActiveText" buttonText="export"
                                          v-on:submit="exportStatus"></common-submit-button>
                </div>
            </div>
        </div>
        <!--Export Button end-->
        <datatable-component class="main-layout-card-content" :options="tableOptions"
                             :exportData="exportToVue" exportFileName="Receiving"
                             @resetStatus="resetExportValue"
                             :tab_name="tabName"
                             :route_name="routeName"></datatable-component>


        <!-- Modal -->
        <!-- <div class="modal fade" id="due-amount-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <cart-due-payment
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :orderType="order_type"
                        :modalID="modalID"
                        :modalTitle="trans('lang.due_total')"
                        @cartItemsToStore = "cartItemsToStore">
                </cart-due-payment>
            </div>
        </div> -->

        <!-- Receive Sale Edit Modal -->
        <div class="modal fade" id="receive-sale-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <cart-receive-sale
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :orderType="order_type"
                        :modalID="modalID"
                        :modalTitle="trans('lang.updates')"
                        @cartItemsToStore = "cartItemsToStore">
                </cart-receive-sale>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete" :message="'service_deleted_permanently'" :firstButtonName="'yes'"
                            :secondButtonName="'no'"
                            @confirmationModalButtonAction="confirmationModalButtonAction"></confirmation-modal>

    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                //modalID: '#due-amount-edit-modal', // due paid
                modalID: '#receive-sale-edit-modal', // receive sale edit
                order_type: 'receiving',
                exportToVue:false,
                hidePreLoader: false,
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tabName : 'receiving_report',
                routeName : 'reports',
                tableOptions: {
                    tableName: 'orders',
                    columns: [
                        {
                            title: 'lang.invoice_id',
                            key: 'invoice_id',
                            type: 'clickable_link',
                            source: 'reports/receiving',
                            uniquefield: 'id',
                            sortable: true
                        },
                        {title: 'lang.received_date', key: 'date', type: 'text', sortable: true},
                        {title: 'lang.received_type', key: 'type', type: 'text', sortable: true},
                        {title: 'lang.received_by', key: 'full_name', type: 'clickable_link',   source: 'user',   uniquefield: 'user_id', sortable: true},
                        {title: 'lang.item_received', key: 'item_purchased', type: 'text', sortable: true},
                        {title: 'lang.total', key: 'total', type: 'text', sortable: true},
                        {title: 'lang.due', key: 'due_amount', type: 'text', sortable: true},
                        {
                            title: 'lang.action',
                            type: 'component',
                            componentName: 'sales-report-action-component'
                        }

                    ],
                    source: '/receiving-report',
                    search: true,
                    sortedBy:'id',
                    sortedType:'DESC',
                    right_align: ['item_purchased', 'total', 'due_amount'],
                    formatting : ['total', 'due_amount'],
                    dateFormatting : ['date'],
                    summary: true,
                    summation:['total','item_purchased', 'due_amount'],
                    summationKey:['id'],
                    filters: [
                        {title: 'lang.date_range', key: 'date_range', type: 'date_range'},

                        {title:'lang.receive_type', type:'dropdown',key:'receive_type',options:[
                                {text: 'lang.all', value: 'all', selected: true},
                                {text: 'lang.supplier', value: 'supplier'},
                                {text: 'lang.internal_receivings', value: 'internal'},
                            ]
                        },
                        {title:'lang.payment_type', type:'dropdown',key:'payment_type',options:[
                                {text: 'lang.all', value: 'all', selected: true},
                                {text: 'lang.paid', value: 'paid'},
                                {text: 'lang.due', value: 'due'},
                            ]
                        }

                    ]
                }
            }
        },

        mounted() {

            let instance = this;

            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });

            /*Due Payment*/
            /*this.$hub.$on('viewSalesReportEdit', function (rowdata) {
                instance.addEditAction(rowdata);
            });*/

            /*Receive Sale Edir*/
            this.$hub.$on('viewSalesReportEdit', function (rowdata) {
                instance.addEditAction(rowdata);
            });
        },

        methods: {

            cartItemsToStore(cartItemsToStore){
                let instance = this;
                instance.hideSalesReturnsPreLoader = false;
                cartItemsToStore.paymentType = 'credit';

                instance.axiosGETorPOST(
                    {
                        url: '/save-due-amount',
                        postData: {cartItemsToStore},
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


            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
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
            confirmationModalButtonAction() {
                this.deleteDataMethod('/sale/delete/' + this.deleteID, this.deleteIndex);
            },

        }
    }

</script>