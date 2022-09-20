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
                        <h5 class="m-0">{{ trans('lang.profit_and_loss') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                       <common-submit-button :buttonLoader="buttonLoader"
                                             :isDisabled="isDisabled"
                                             :isActiveText="isActiveText"
                                             buttonText="export"
                                             v-on:submit="exportStatus">
                       </common-submit-button>
                    </div>
                </div>
            </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 :exportFileName="trans('lang.profit')"
                                 @resetStatus="resetExportValue"
                                 :tab_name="tabName"
                                 :route_name="routeName">
            </datatable-component>
        </span>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                exportToVue: false,
                isActive: false,
                selectedItemId: '',
                tableOptions: {},
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tabName:'profit_loss_report',
                routeName:'reports',
                hasData: value => {
                    return !_.isEmpty(value) ? true : false
                },
            }
        },
        created() {
            let instance = this;
            instance.getData();
        },
        mounted() {

        },
        methods: {

            getData() {
                let instance = this;
                instance.axiosGet('/branch-list',
                    function (response) {
                        if (response.data) {
                            let branches = [{text: 'All', value: 'all', selected: true}, ...response.data];
                            instance.tableOptions = {
                                tableName: 'orders',
                                columns: [
                                    {
                                        title: 'lang.invoice_id',
                                        key: 'invoice_id',
                                        type: 'clickable_link',
                                        source: 'reports/sales',
                                        uniquefield: 'sales_id',
                                        sortable: true
                                    },
                                    {title: 'lang.date', key: 'sales_date', type: 'text', sortable: true},
                                    {title: 'lang.branch', key: 'branch_name', type: 'text', sortable: true},
                                    {title: 'lang.grand_total', key: 'grand_total', type: 'text', sortable: true},
                                    {title: 'lang.vat', key: 'item_tax', type: 'text', sortable: true},
                                    {title: 'lang.profit_amount', key: 'profit_amount', type: 'text', sortable: true},
                                ],
                                source: '/profit-loss-report',
                                search: true,
                                summary: true,
                                formatting: ['grand_total', 'item_tax', 'profit_amount'],
                                dateFormatting: ['sales_date'],
                                summation: ['grand_total', 'item_tax', 'profit_amount'],
                                sortedBy: 'sales_id',
                                sortedType: 'DESC',
                                summationKey: ['invoice_id'],
                                right_align: ['grand_total', 'item_tax', 'profit_amount'],
                                filters: [
                                    {
                                        title: 'lang.date_range', key: 'date_range', type: 'date_range'
                                    },
                                    {
                                        title: 'lang.branch',
                                        key: 'branch',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: branches
                                    },

                                ]
                            }
                        }
                        instance.setPreLoader(true);
                    },
                    function (error) {
                        instance.setPreLoader(true);
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
            }
        }
    }

</script>