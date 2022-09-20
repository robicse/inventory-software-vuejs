<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h5 class="bluish-text" v-if="id">{{ trans('lang.edit_group') }}</h5>
                        <h5 class="bluish-text" v-else="id">{{ trans('lang.add_new_group') }}</h5>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <span aria-hidden="true"><i class="la la-close"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row" v-else>
                <div class="form-group col-md-6">
                    <label for="title">{{ trans('lang.title') }}</label>
                    <input v-validate="'required'" name="title" id="title" type="text" class="form-control"
                           v-model="title" :class="{ 'is-invalid': submitted && errors.has('title') }">
                    <div class="heightError" v-if="submitted && errors.has('title')">
                        <small class="text-danger" v-show="errors.has('title')">{{ errors.first('title') }}</small>
                    </div>
                </div>
                <div class="form-group  col-md-6">
                    <label for="discount">{{ trans('lang.discount') }} %</label>
                    <common-input v-validate="'required'"
                                  name="discount"
                                  id="discount"
                                  :inputValue="decimalFormat(discount)"
                                  @input="setPercentageValue">
                    </common-input>
                    <!--<input v-validate="'required'" name="discount" id="discount" type="number" class="form-control" v-model="discount">-->
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('discount')">{{ errors.first('discount') }}
                        </small>
                    </div>
                </div>
                <div class="form-group margin-top col-12">
                    <label>{{ trans('lang.is_default') }}</label><br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" name="isDefault" class="custom-control-input" id="default" checked="checked"
                               value="1" v-model="isDefault">
                        <label class="custom-control-label" for="default">{{ trans('lang.yes') }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" name="isDefault" class="custom-control-input" id="not_default"
                               :disabled="tempIsDefault==1" value="0" v-model="isDefault">
                        <label class="custom-control-label" for="not_default">{{ trans('lang.no') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.save')
                        }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{
                        trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: ['id', 'modalID',],
        extends: axiosGetPost,

        data() {
            return {
                title: '',
                discount: '',
                isDefault: 0,
                tempIsDefault: '',
                submitted:false
            }
        },

        created() {
            if (this.id) {
                this.getGroupData('/groups/' + this.id);
            }
        },
        methods: {
            save() {
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            title: this.title,
                            discount: this.discount,
                            is_default: this.isDefault,
                        };

                        if (this.id) {
                            this.postDataMethod('/group/' + this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod('/group/store', this.inputFields);

                        }
                    }
                });
            },
            getGroupData(route) {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.title = response.data.title;
                        instance.discount = response.data.discount;
                        instance.isDefault = response.data.is_default;
                        instance.tempIsDefault = response.data.is_default;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response = null) {
                $(this.modalID).modal('hide');
                this.$hub.$emit('reloadDataTable');

            },
            postDataCatchFunctionality(error = null) {
                this.showErrorAlert(error.data.message);
            },
            setPercentageValue(amount){
                this.discount = amount;
            }

        },
    }

</script>