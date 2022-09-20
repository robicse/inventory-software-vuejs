<template>
    <div class="back-img">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8 col-xl-4 offset-xl-8">
                    <div class="sign-in-sign-up-content">
                        <form class="sign-in-sign-up-form">
                            <div class="form-row">
                                <div class="form-group col s12">
                                    <h5 class="text-center">{{ trans('lang.forgot_password') }}</h5>
                                    <h6 class="text-center">{{ trans('lang.enter_email_address') }}</h6>
                                </div>
                            </div>
                            <span v-if="alertMessage.length>0" class="alertBranch">
                                <div class="alert alert-warning alertBranch" role="alert">
                                    {{alertMessage}}
                                </div>
                            </span>
                            <div class="form-row">
                                <div class="form-group col s12">
                                    <label for="email"> {{ trans('lang.login_email') }}</label>
                                    <input id="email" v-validate="'required'" v-model="email" type="email" name="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': submitted && errors.has('email') }">
                                    <div class="heightError" v-if="submitted && errors.has('email')">
                                        <small class="text-danger" v-show="errors.has('email')">{{ errors.first('email')
                                            }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-row">
                                    <div class="form-group col-12">
                                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                                              :isActiveText="isActiveText" buttonText="submit"
                                                              v-on:submit="passRecover"></common-submit-button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col s12">
                                    <span>{{ trans('lang.if_you_remember_your_password') }}</span> &nbsp <a href="#"
                                                                                                            @click="login"
                                                                                                            class="bluish-text">{{
                                    trans('lang.login') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
                email: '',
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                alertMessage: false,
                submitted: false,
            }
        },
        created() {
        },
        methods:
            {
                passRecover() {
                    this.submitted = true;
                    this.$validator.validateAll().then((result) => {
                        if (result) {
                            this.buttonLoader = true;
                            this.isDisabled = true;
                            this.isActiveText = true;

                            let instance = this;
                            instance.loginPostMethod('/recover', {
                                    email: this.email
                                }
                            );
                        }
                    });
                },
                loginPostSucces(response) {
                    let instance = this;
                    instance.showSuccessAlert(response.data.data.message);
                    instance.buttonLoader = false;
                    instance.isDisabled = false;
                    instance.isActiveText = false;
                },
                loginPostError(response) {

                    let instance = this;
                    instance.buttonLoader = false;
                    instance.isDisabled = false;
                    instance.isActiveText = false;
                    instance.alertMessage = response.data.error;
                },
                login() {
                    let instance = this;
                    instance.redirect('/');
                }
            }
    }
</script>