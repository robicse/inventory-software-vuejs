<template>
    <div class="main-layout-card-content-wrapper">
        <div class="text-right disable_button">
            <label class="switch">
                <input type="checkbox" v-model="isDisableShortcut" @click="disableAllShortCuts">
                <span class="slider"></span>
            </label>
        </div>
        <div class="main-layout-card-content shortcut_card shortcut_button">
            <pre-loader v-if="hidePreloader"></pre-loader>
            <form v-else >
                <div class="mb-3 overflow-s " id="shortcut-settings">
                    <div class="form-row sales_shortcut">
                        <h6 class="col">{{ trans('lang.enable_shortcut') }}</h6>
                    </div>
                    <hr>
                    <div :class="[isDisableShortcut ? false : 'disabledbutton']">
                        <div class="mb-3">
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-md-8 col-sm-8 ab">
                                        <label > {{ trans('lang.product_search') }}</label>
                                        <input type="text" id="productSearch" class="form-control" value="One"
                                               v-model="shortcutsInfo.productSearch.shortcut_key"
                                               v-on:keyup="showUnprintableValue(id='#productSearch')" @click="inputClick(1)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.productSearch.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='1' " class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="productSearchRadio"
                                               v-model="shortcutsInfo.productSearch.status">
                                        <label>{{trans('lang.enable')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-md-8 col-sm-8 ab">
                                        <label>{{ trans('lang.hold_card') }}</label>
                                        <input type="text" class="form-control"
                                               v-model="shortcutsInfo.holdCard.shortcut_key" id="holdCardId"
                                               v-on:keyup="showUnprintableValue(id='#holdCardId')" @click="inputClick(2)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.holdCard.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='2'"  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="holdCardRadio" v-model="shortcutsInfo.holdCard.status" @click="inputClick(3)">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-md-8 col-sm-8 ab">
                                        <label> {{ trans('lang.pay') }}</label>
                                        <input type="text" class="form-control" v-model="shortcutsInfo.pay.shortcut_key"
                                               id="pay"
                                               v-on:keyup="showUnprintableValue(id='#pay')" @click="inputClick(3)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection,shortcutsInfo.pay.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='3' "  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="payRadio" v-model="shortcutsInfo.pay.status">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-lg-8 col-md-8 col-sm-8 ab">
                                        <label> {{ trans('lang.add_customer') }}</label>
                                        <input type="text" class="form-control"
                                               v-model="shortcutsInfo.addCustomer.shortcut_key"
                                               id="addCustomer"
                                               v-on:keyup="showUnprintableValue(id='#addCustomer')" @click="inputClick(4)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.addCustomer.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='4'"  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg -4  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="addCustomerRadio"
                                               v-model="shortcutsInfo.addCustomer.status">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-md-8 col-sm-8 ab">
                                        <label> {{trans('lang.cancel_card_item')}}</label>
                                        <input type="text" class="form-control"
                                               v-model="shortcutsInfo.cancelCarditem.shortcut_key"
                                               id="cancelCarditem"
                                               v-on:keyup="showUnprintableValue(id='#cancelCarditem')" @click="inputClick(5)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.cancelCarditem.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='5'"  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="cancelCarditemRadio"
                                               v-model="shortcutsInfo.cancelCarditem.status">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group col-md-8  col-sm-8 ab">
                                        <label> {{trans('lang.load_sales_page')}}</label>
                                        <input type="text" class="form-control"
                                               v-model="shortcutsInfo.loadSalesPage.shortcut_key"
                                               id="loadSalesPage"
                                               v-on:keyup="showUnprintableValue(id='#loadSalesPage')" @click="inputClick(6)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.loadSalesPage.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='6' "  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="loadSalesPageRadio"
                                               v-model="shortcutsInfo.loadSalesPage.status">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-row">
                                <div class="col-md-6 col-sm-6 ">
                                    <div class="form-group col-md-8 col-sm-8  ab">
                                        <label> {{trans('lang.done_payment')}}</label>
                                        <input type="text" class="form-control"
                                               v-model="shortcutsInfo.donePayment1.shortcut_key"
                                               id="donePayment1"
                                               v-on:keyup="showUnprintableValue(id='#donePayment1')" @click="inputClick(7)">
                                        <div class="heightError text-nowrap">
                                            <small class="text-danger"
                                                   v-show="includes(duplicateShortCutCollection, shortcutsInfo.donePayment1.shortcut_key)">
                                                {{ trans('lang.shortcut_key_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div v-if="isShortcutAbleKey && inputFieldValue=='7'"  class="heightError text-nowrap">
                                            <small class="text-danger">
                                                {{ trans('lang.use_ctrl_for_combination_key') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="form-group  col-md-4 col-sm-4 re">
                                        <input type="checkbox" id="donePayment1Radio"
                                               v-model="shortcutsInfo.donePayment1.status">
                                        <label> {{trans('lang.enable')}} </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                   </div>
                </div>
            </form>
            <div class="col-12">
                <button class="btn btn-primary app-color mobile-btn save_button" @click="save">{{ trans('lang.save') }}</button>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        data() {
            return {
                item: {},
                shortCut: {},
                hidePreloader: false,
                productSearchId: '',
                checked: '',
                isDisableShortcut: '',
                shortcutsInfo: {},
                isShortcutsActive:3,
                isShortcutAbleKey:'',
                inputFieldValue:'',
                duplicateShortCutCollection: [],
                includes: (array, value) => {
                    if (_.includes(array, value)) {
                        return true;
                    }
                    return false;
                },
            }
        },
        created() {
            this.getkeyboardShortcutSettingsData('/shortcut-setting-data/{id}');
        },
        mounted(){
            let instance = this;
            instance.hidePreloader = 'hide';
            this.showUnprintableValue();
        },
        watch:{
            shortcutsInfo: {
                handler(val){
                    this.duplicateShortCutCollection = [];
                },
                deep: true
            }
        },
        methods: {
            disableAllShortCuts() {
                if (this.isDisableShortcut){
                    this.saveButtonDisabled = true;
                    this.isDisableShortcut = false;
                }else{
                    this.isDisableShortcut = true;
                }
            },
            getkeyboardShortcutSettingsData(route){
                let instance = this;
                instance.hidePreloader = true;
                this.axiosGet(route,
                    function (response){
                        if (response.data.shortcutStatus == 0){
                            instance.isDisableShortcut = false;
                        }else{
                            instance.isDisableShortcut = true;
                        }
                        instance.shortcutsInfo = response.data.shortcutSettings;
                        instance.hidePreloader = false;
                    },
                    function (error) {
                        instance.hidePreloader = false;
                    },
                );
            },
            showUnprintableValue(id){
                this.isShortcutAbleKey = this.keyboardAscciValueReader(id);
            },
            save(){
                let instance = this;
                instance.hidePreloader = true;
                this.inputFields = {
                      productSearch:{
                        action_name: 'productSearch',
                        shortcut_key: instance.shortcutsInfo.productSearch.shortcut_key,
                        status: instance.shortcutsInfo.productSearch.status
                    },
                    holdCard: {
                        action_name: 'holdCard',
                        shortcut_key: instance.shortcutsInfo.holdCard.shortcut_key,
                        status: instance.shortcutsInfo.holdCard.status
                    },
                    pay: {
                        action_name: 'pay',
                        shortcut_key: instance.shortcutsInfo.pay.shortcut_key,
                        status: instance.shortcutsInfo.pay.status
                    },
                    addCustomer: {
                        action_name: 'addCustomer',
                        shortcut_key: instance.shortcutsInfo.addCustomer.shortcut_key,
                        status: instance.shortcutsInfo.addCustomer.status
                    },
                    cancelCarditem: {
                        action_name: 'cancelCarditem',
                        shortcut_key: instance.shortcutsInfo.cancelCarditem.shortcut_key,
                        status: instance.shortcutsInfo.cancelCarditem.status
                    },
                    loadSalesPage: {
                        action_name: 'loadSalesPage',
                        shortcut_key: instance.shortcutsInfo.loadSalesPage.shortcut_key,
                        status: instance.shortcutsInfo.loadSalesPage.status
                    },
                    donePayment1: {
                        action_name: 'donePayment1',
                        shortcut_key: instance.shortcutsInfo.donePayment1.shortcut_key,
                        status: instance.shortcutsInfo.donePayment1.status
                    },
                };
                let duplicates = [];
                _.forIn(this.inputFields, function (value, key) {
                    _.compact(duplicates.push(value.shortcut_key));
                });
                instance.duplicateShortCutCollection = instance.find_duplicate_in_array(duplicates);
                if(instance.duplicateShortCutCollection.length === 0){
                    this.postDataMethod('/shortcuts', {
                        shortcut: this.inputFields,
                        shortcutStatus: this.isDisableShortcut
                    });
                }else{
                    instance.hidePreloader = false;
                }
            },
            postDataThenFunctionality(response) {
                let instance = this;
                instance.redirect("/myprofile");
                this.$emit("shortcut", this.isShortcutsActive);
                instance.hidePreloader = false;
            },
            postDataCatchFunctionality(error) {
                let instance = this;
                instance.hidePreloader = false;
            },
            inputClick(val){
               this.inputFieldValue = val;
            }
        }
    }
</script>
