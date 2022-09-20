<template>
    <div class="p-3">
        <div class="row mb-4">
            <div class="col-md-11">
                <!-- get data from parent component (rowdata)-->
                <!-- {{ rowdata }} -->

                <!-- get data from this component --> 
                <!-- {{ orderLists }}
                {{ orderDetailsLists }} -->

                <h5>{{ modalTitle }}</h5>
            </div>
            <div class="col-md-1 text-right">
                <a href="#" data-dismiss="modal" aria-label="Close"
                   class="position-absolute variant-modal-close-btn close"><i class="la la-close text-grey"></i></a>
            </div>
        </div>

        <pre-loader v-if="!hidePaymentListGetLoader"></pre-loader>

        <div v-else>
            <form class="form-row">
                <div class="row">
                    <table class="table custom-table-responsive">
                        <thead>
                            <tr>
                                <td>Item</td>
                                <td>Qty</td>
                                <td>Price</td>
                                <!-- <td>Discount</td>
                                <td>Total</td> -->
                            </tr>
                        </thead>
                        <tbody v-if="orderDetailsLists.length > 0">
                            <tr v-for="(data,index) in orderDetailsLists" v-show="data.type != 'discount'">
                                <td>
                                    <!-- <input type="text" name="product" v-model="data.title" class="form-control" /> -->
                                    {{ data.title }}
                                </td>
                                <td>
                                    <!-- <input type="text" name="quantity[]" v-model="data.quantity" class="form-control" /> -->
                                    <!-- <input v-validate="'required'" name="quantity[]" type="text" class="form-control" v-model="data.quantity" :class="{ 'is-invalid': submitted && errors.has('quantity') }">
                                    <div v-if="submitted && errors.has('quantity')" class="heightError">
                                        <small class="text-danger" v-show="errors.has('quantity')">{{ errors.first('quantity') }}</small>
                                    </div> -->



                                    <a href="#"
                                       :class=""
                                       @click.prevent="orderQuentityChance(data,data.id,'-')">
                                        <i class="la la-minus-circle la-2x cart-icon-color"></i>
                                    </a>
                                    <span id="">{{ data.quantity }}</span>
                                    <a href="#"
                                       :class=""
                                       @click.prevent="orderQuentityChance(data,data.id,'+')">
                                        <i class="la la-plus-circle la-2x cart-icon-color"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ data.price }}
                                </td>
                                <!-- <td>
                                    {{ data.discount }}
                                </td>
                                <td>
                                    {{ data.total }}
                                </td> -->
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td><button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.updates') }}</button></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row">
                    <!-- <table class="table custom-table-responsive">
                        <tbody>
                            <tr>
                                <td>Sub Total</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    {{ orderLists.sub_total }}
                                </td>
                            </tr>
                            <tr>
                                <td>Vat</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    {{ orderLists.total_tax }}
                                </td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    {{ orderLists.total }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="clear"></div> -->
                    <!-- <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.updates') }}</button> -->
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    const {unformat} = require('number-currency-format');
    export default {
        extends: axiosGetPost,
        props: ['rowdata', 'modalID', 'modalTitle', 'orderType'],
        data() {
            return {
                hidePaymentListGetLoader: null,
                selectedRowData: this.rowdata,
                selectedModalId: this.modalID,
                orderLists: {},
                orderDetailsLists: {},
                quantity:[],
                submitted:false,
            };
        },
        created() {

            if (this.selectedRowData.id) {
                /*console.log('okkk');*/
                this.getOrderEditData('/reports/OrderEditData/' + this.selectedRowData.id);
            }
        },
        mounted() {
            
        },
        methods: {
               
            getOrderEditData(route) {

                this.hidePaymentListGetLoader = false;
                let instance = this;
                
                instance.axiosGet(route,
                    function (response) {
                        console.log(response);
                        instance.orderLists = response.data.orders
                        instance.orderDetailsLists = response.data.datarows
                    },
                    
                );
                instance.hidePaymentListGetLoader = true;
            },

            orderQuentityChance(orderData, orderItemId, action) {
                //console.log('coming...');
                //alert(orderItemId);
                //console.log(orderItemId);
                let instance = this;
                
                this.orderDetailsLists.forEach(function (orderItem, index, orderArray) {
                    //console.log(orderItem);
                    if (orderItem.id == orderItemId) {
                        if (action == '+') {
                            orderArray[index].quantity++;
                            if (orderItem.quantity == 0) {
                                orderArray.splice(index, 1);
                            }
                        }

                        if (action == '-') {
                            --orderArray[index].quantity;

                            if (orderItem.quantity == 0) {
                                orderArray.splice(index, 1);
                            }
                        }
                    }
                });

            },

            save()
            {
                let instance = this;
                instance.submitted =true;
                instance.$validator.validateAll().then((result) => {
                    if (result) {
                        console.log('go here');
                        console.log(instance.orderDetailsLists);    
                        this.postDataMethod('/reports/updateOrderDetails',instance.orderDetailsLists);
                    }
                });

            },
            postDataThenFunctionality(response)
            {
                //alert('success');
                $(this.modalID).modal('hide');
            },
            postDataCatchFunctionality(error){
                let instance = this;
                //alert('error');
            },

        },

    }
</script>
