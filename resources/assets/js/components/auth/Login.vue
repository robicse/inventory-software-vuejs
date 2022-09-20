<template>
    <div class="back-img">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8 col-xl-4 offset-xl-8">
                    <div class="sign-in-sign-up-content">
                        <form class="sign-in-sign-up-form">
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <!--<h1 class="text-center">DreamZon</h1>-->
                                    <h1 class="text-center">
                                        <img src="http://boxtechsoftware.top/uploads/logo/logo_53063939.png" width="200" height="auto" alt="img prob">
                                    </h1>
                                    <h5 class="text-center">{{ trans('lang.login') }}</h5>
                                </div>
                            </div>
                            <span v-if="alertMessage.length>0" class="alertBranch">
                                <div class="alert alert-warning alertBranch" role="alert">
                                    {{alertMessage}}
                                </div>
                            </span>
                            <div class="form-row">
                                <div class="form-group col-12">
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
                            <div class="form-row margin-top">
                                <div class="form-group col-12">
                                    <label for="password">{{ trans('lang.login_password') }}</label>
                                    <input id="password" v-validate="'required'" ref="password" v-model="password"
                                           name="password" type="password" class="form-control"
                                           :class="{ 'is-invalid': submitted && errors.has('password') }">
                                    <div class="heightError">
                                        <small class="text-danger" v-show="errors.has('password')">{{
                                            errors.first('password') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row loginButton">
                                <div class="form-group col-12">
                                    <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                                          :isActiveText="isActiveText" buttonText="login"
                                                          v-on:submit="loginPost"></common-submit-button>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <span>{{ trans('lang.have_you_forgot_your_password')}} </span>
                                    <a @click="forgetPassword" href="#" class="bluish-text">{{ trans('lang.click_here')
                                        }}</a>
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
                name: '',
                password: '',
                remember: true,
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                preLoaderType: 'load',
                hidePreLoader: false,
                isActive: 'active',
                alertMessage: '',
                submitted: false
            }
        },
        methods: {

            loginPost() {
                this.submitted = true,
                    this.$validator.validateAll().then((result) => {
                        if (result) {
                            this.inputFields = {
                                email: this.email,
                                password: this.password,
                            };
                            this.buttonLoader = true;
                            this.isDisabled = true;
                            this.isActiveText = true;
                            this.loginPostMethod('/', {
                                    email: this.email,
                                    password: this.password
                                }
                            );
                        }
                    });
            },

            forgetPassword() {
                let instance = this;
                instance.redirect('/password/reset');
            },
            loginPostSucces(response) {
                let instance = this;
                instance.redirect("/dashboard");
            },
            loginPostError(response) {
                let instance = this;
                instance.buttonLoader = false;
                instance.isDisabled = false;
                instance.isActiveText = false;
                instance.alertMessage = response.data.errors.email[0];
            },
        }
    }
</script>