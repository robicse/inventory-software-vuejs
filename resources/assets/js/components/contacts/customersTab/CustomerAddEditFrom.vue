<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h4 class="m-0" v-if="id">{{ trans('lang.edit_customer') }}</h4>
                        <h4 class="m-0" v-else>{{ trans('lang.add_customer') }}</h4>
                    </div>
                    <div class="col-2 text-right pr-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <span aria-hidden="true"><i class="la la-close"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">

            <pre-loader v-if="!hidePreLoader"></pre-loader>

            <form class="form-row" v-else>
                <div class="form-group col-md-6">
                    <label for="first_name">{{ trans('lang.first_name') }}</label>
                    <input v-validate="'required'" name="firstname"  id="first_name" type="text" class="form-control" v-model="firstName"
                           :class="{ 'is-invalid': submitted && errors.has('firstname') }">
                    <div v-if="submitted && errors.has('firstname')" class="heightError"><small class="text-danger" v-show="errors.has('firstname')">{{ errors.first('firstname') }}</small></div>
                </div>
                <div class="form-group col-md-6">
                    <label for="last_name">{{ trans('lang.last_name') }}</label>
                    <input v-validate="'required'" name="lastname" data-vv-as="last name" id="last_name" v-model="lastName" type="text" class="form-control"
                           :class="{ 'is-invalid': submitted && errors.has('lastname') }">
                    <div v-if="submitted && errors.has('lastname')" class="heightError"><small class="text-danger" v-show="errors.has('lastname')">{{ errors.first('lastname') }}</small></div>
                </div>
                <div class="form-group margin-top col-md-12">
                    <label for="email">{{ trans('lang.customer_email') }}</label>
                    <input v-validate="'email'" name="email" id="email" type="text" class="form-control" v-model="email" :class="{ 'is-invalid': submitted && errors.has('email') }">
                    <div class="heightError" v-if="submitted && errors.has('email')"> <small class="text-danger" v-show="errors.has('email')">{{ errors.first('email') }}</small></div>
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="phoneNumber">{{ trans('lang.phone_number') }}</label>
                    <input id="phoneNumber" type="text" class="form-control" v-model="phoneNumber">
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="address">{{ trans('lang.customer_address') }}</label>
                    <input id="address" type="text" class="form-control" v-model="address">
                </div>
                <div class="form-group col-md-6">
                    <label for="company">{{ trans('lang.customer_company') }}</label>
                    <input id="company" type="text" class="form-control" v-model="company">
                </div>
                <div class="form-group col-md-6">
                    <label for="customer-group">{{ trans('lang.customer_group') }}</label>
                    <select v-validate="'required'" v-model="customerGroup" name="customer-group" data-vv-as="customer group" id="customer-group" class="form-control">
                        <option :value="group.id" v-for="group in groups"> {{ group.title }} </option>
                    </select>
                    <div v-if="submitted && errors.has('customer-group')" class="heightError">
                        <small class="text-danger" v-show="errors.has('customer-group')">{{ errors.first('customer-group') }}</small>
                    </div>
                </div>
                <!-- <div class="form-group col-md-6">
                    <label for="test">Test</label>
                    <input id="test" type="text" class="form-control" v-model="test">
                </div> -->
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.save') }}</button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{ trans('lang.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        props:['id','modalID','order_type'],
        extends: axiosGetPost,
        data(){
            return{

                firstName:'',
                lastName:'',
                email:'',
                phoneNumber:'',
                address:'',
                company:'',
                //test:'',
                customerGroup:'',
                groups:[],
                customer:[],
                submitted:false,
            }
        },
        created(){
            this.getGroupsData();
            if(this.id)
            {
                this.getCustomerData('/customer-data/'+this.id);
            }
        },
        methods : {

            save()
            {
                this.submitted =true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                        first_name: this.firstName,
                        last_name: this.lastName,
                        email: this.email,
                        company: this.company,
                        //test: this.test,
                        phone_number: this.phoneNumber,
                        address: this.address,
                        customer_group: this.customerGroup,
                        };

                        if(this.id)
                        {
                            this.postDataMethod('/customer/'+this.id, this.inputFields);
                        }
                        else
                        {
                            this.postDataMethod('/customer/store',this.inputFields);
                        }
                    }
                });

            },
            postDataThenFunctionality(response)
            {
                $(this.modalID).modal('hide');
                if(this.order_type !='sales'){
                    this.$hub.$emit('customerAddedFromSales');
                    this.$hub.$emit('reloadDataTable');
                }

                this.$emit('newCustomer',response.data.id);
            },
            postDataCatchFunctionality(error){
                let instance = this;
            },
            getGroupsData() {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet('/groups/',
                    function(response){
                        instance.groups = response.data;
                        if(instance.id){

                        }
                        else{
                            instance.setPreLoader(true);
                            instance.groups.forEach(function (group) {
                                if (group.is_default == 1) {
                                    instance.customerGroup = group.id;
                                }
                            });
                        }
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            getCustomerData(route){

                    let instance = this;
                    instance.setPreLoader(false);
                    instance.axiosGet(route,
                        function(response){
                            instance.firstName = response.data.customer.first_name;
                            instance.lastName = response.data.customer.last_name;
                            instance.email = response.data.customer.email;
                            instance.phoneNumber = response.data.customer.phone_number;
                            instance.address = response.data.customer.address;
                            instance.company = response.data.customer.company;
                            //instance.test = response.data.customer.test;
                            instance.customerGroup = response.data.customer.customer_group;
                            instance.setPreLoader(true);
                        },
                        function (response) {
                            instance.setPreLoader(true);
                        },
                    );
            }

        },
    }

</script>