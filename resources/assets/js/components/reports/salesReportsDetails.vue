<template>
    <div>
        <div>
            <div class="main-layout-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent m-0">
                        <li class="breadcrumb-item">
                    <span v-if="order_type=='sales'">{{trans('lang.sales_details')}} (
                        <a href="#" @click="goBack">{{trans('lang.back_page')}}</a>
                        )
                    </span>
                        <span v-else>{{trans('lang.receives_details')}}(
                        <a href="#" @click="goBack">{{trans('lang.back_page')}}</a>
                        )
                    </span>
                        </li>
                    </ol>
                </nav>
                <div class="main-layout-card">
                    <span v-if="showPreloader"><pre-loader></pre-loader></span>
                        <span v-else>
                            <div class="custom-tabs salesReportsDetails">
                                <h5 class="text-center" v-if="order_type=='sales'">{{trans('lang.sales_details')}}</h5>
                                <h5 class="text-center" v-else>{{trans('lang.receives_details')}}</h5>
                                <h6 class="text-center">{{ordersDetailsData.orders_details.date}}</h6>
                                <!--<h6 class="text-center">{{ trans('lang.order_id') }} : {{ordersDetailsData.orders_details.id}}</h6>-->
                                <h6 class="text-center" v-if="order_type=='sales'">{{ trans('lang.sales_reports_sold_by') }} : {{ordersDetailsData.orders_details.first_name}} {{ordersDetailsData.orders_details.last_name}}</h6>
                                <h6 class="text-center" v-else>{{ trans('lang.receiving_by') }} : {{ordersDetailsData.orders_details.first_name}} {{ordersDetailsData.orders_details.last_name}}</h6>
                                <h6 class="text-center">{{ trans('lang.invoice_id') }} : {{ordersDetailsData.orders_details.invoice_id}}</h6>
                            </div>
                            <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>
                        </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['id', 'order_type','tab_name', 'route_name'],
        data() {
            return {
                tableOptions: {
                    tableName: 'Order Items',
                    columns: [
                        {title: 'lang.sales_reports_item_name', key: 'title', type: 'text', sortable: false},
                        {title: 'lang.item_qtty', key: 'quantity', type: 'text', sortable: false},
                        {title: 'lang.item_price', key: 'price', type: 'text', sortable: false},
                        {title: 'lang.discount', key: 'discount', type: 'text', sortable: false},
                        {title: 'lang.item_total', key: 'total', type: 'text', sortable: false},
                    ],
                    source: '/reports/salesDetails/' + this.id,
                    formatting : ['total'],
                    right_align: ['quantity','price','discount','total']
                },
                ordersDetailsData: {},
                showPreloader:true,
                tabName:'',
                routeName:'',
            }
        },
        methods: {
            getOrdersInfo() {
                let instance = this;
                this.axiosGet('/reports/ordersDetails/' + instance.id,
                    function (response) {
                        /*console.log(response.data);*/
                        instance.ordersDetailsData = response.data;
                        instance.showPreloader = false;
                    },
                    function (response) {

                    },
                );
            },
            goBack() {
                let instance = this;
                instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&${this.routeName}`);
            }
        },
        mounted() {
            this.tabName = this.tab_name;
            this.routeName = this.route_name;

            this.getOrdersInfo();
        }
    }
</script>