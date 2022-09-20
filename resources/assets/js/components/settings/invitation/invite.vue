<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h5 class="m-0" v-if="id">{{ trans('lang.change_user_role') }}</h5>
                        <h5 class="m-0" v-else>{{ trans('lang.invite_user') }}</h5>
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
                <div class="form-group col-md-12" v-if="!id">
                    <label for="invitation-email">{{ trans('lang.login_email') }}</label>
                    <input id="invitation-email" v-validate="'required'" name="email" class="form-control" type="email" v-model="email" :class="{ 'is-invalid': submitted && errors.has('email') }">
                    <div class="heightError" v-if="submitted && errors.has('email')"><small class="text-danger" v-show="errors.has('email')">{{ errors.first('email') }}</small></div>
                </div>
                <div class="form-group col-md-12" v-else>
                    <label>{{ trans('lang.name') }}</label>
                    <h6 class="m-0"> {{name}} </h6>
                </div>
                <div class="form-group maergin-top col-md-12">
                    <label for="roles">{{ trans('lang.role') }}</label>
                    <select v-model="inviteAs" v-validate="'required'" name="inviteAs" data-vv-as="role" id="roles" class="form-control">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="role in roles" :value="role.id"> {{role.title}} </option>
                    </select>
                    <div class="heightError"  ><small class="text-danger" v-show="errors.has('inviteAs')">{{ errors.first('inviteAs') }}</small></div>
                </div>

                <div class="form-group margin-top col-md-12">
                    <label>{{trans('lang.branch')}}</label>

                    <div v-for="branch in branches" class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" :id="branch.id" v-model="branchPermission" :value="branch.id"/>
                        <label class="custom-control-label" :for="branch.id">{{ branch.name }}</label> <br>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="inviteUser()">{{ buttonName() }}</button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{ trans('lang.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props:['id','modalID'],
        extends: axiosGetPost,

        data()
        {
            return {
                email:'',
                inviteAs:'',
                branchPermission:[],
                roles:[],
                branches:[],
                name:'',
                submitted: false,
            }
        },

        created()
        {
            this.getRolesData();
            this.getBranchData();
            if(this.id)
            {
                this.getUserData('/get-user/' + this.id);
            }
        },


        methods:{

            buttonName(){
                if(this.id)
                {
                    return this.trans('lang.save');
                }
                else
                {
                    return this.trans('lang.invite_button');
                }
            },

            inviteUser() {
                this.submitted= true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            email: this.email,
                            inviteAs:this.inviteAs,
                        };
                        if (this.id)
                        {
                            this.inputFields = {
                                role_id: this.inviteAs,
                                branchID: _.compact(this.branchPermission),

                            };
                            this.postDataMethod('/roleassign/' + this.id, this.inputFields);
                        }
                        else
                        {
                            this.inputFields = {

                                email: this.email,
                                invited_as: this.inviteAs,
                                branchID: this.branchPermission,
                            };
                            this.postDataMethod('/invite', this.inputFields);
                        }
                    }
                });
            },
            postDataThenFunctionality(response)
            {
                $(this.modalID).modal('hide');

                this.$hub.$emit('reloadDataTable');

            },
            postDataCatchFunctionality(error)
            {
                this.hidePreLoader = false;
                this.showErrorAlert(error.data.message);
            },
            getRolesData()
            {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet('/allroleid',
                    function(response)
                    {
                        instance.roles = response.data;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            getBranchData()
            {
                let instance = this;

                instance.axiosGet('/branches',
                    function(response)
                    {
                        instance.branches = response.data;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },

            is_disable()
            {
                this.is_disabled = true;
            },
            getUserData(route)
            {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function(response){
                        instance.name =response.data.rowUser.first_name + ' ' + response.data.rowUser.last_name;
                        instance.inviteAs = response.data.rowUser.role_id;
                        instance.branchPermission = response.data.branchId;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            }
        }
    }
</script>