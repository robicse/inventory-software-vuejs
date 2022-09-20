<template>
    <div>
        <div class="main-layout-wrapper" v-shortkey="loadSales"
             @shortkey="globalShortcutMethod()">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.settings')}}</span>
                    </li>
                </ol>
            </nav>
            <div class="container-fluid pr-0 mt-0">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-3 col-xl-2 settings-left-card">
                        <div class="main-layout-card">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.settings') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <ul class="list-group list-group-flush" id="settings-list">
                                    <li class="list-group-item" :class="{'active-border':isSelectedTab(tab.name)}"
                                        @click.prevent="selectTab(tab.name, tab.component)" v-if="isVisible(tab.name)"
                                        v-for="tab in tabs">
                                        {{ trans(tab.lang) }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-9 col-xl-10 px-0">
                        <transition name="slide-fade" mode="out-in">
                            <component v-if="this.componentName" v-bind:is="this.componentName"></component>
                        </transition>
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

        props: ['roles', 'app_settings', 'email_settings', 'email_templates',
            'payment_settings', 'tax_settings', 'branches_setting', 'users',
            'cash_register', 'invoice_settings', 'invoice_templates', 'product_settings',
            'shortcuts_setting', 'updates_setting', 'tab_name','route_name'],

        data() {

            return {
                demo: [],
                selectedTab: null,
                componentName: null,
                loadSales:[],
                tabs: [
                    {name: "app_settings", lang: "lang.application", component: "application-setting"},
                    {name: "email_settings", lang: "lang.emails", component: "email-setting"},
                    {name: "email_templates", lang: "lang.email_templates", component: "email-template-list"},
                    {name: "roles", lang: "lang.roles", component: "roles-index"},
                    {name: "users", lang: "lang.users", component: "user-list"},
                    {name: "tax_settings", lang: "lang.vat", component: "all-taxes"},
                    {name: "branches_setting", lang: "lang.branches", component: "branches"},
                    {name: "cash_register", lang: "lang.cash_registers", component: "cash-register"},
                    {name: "payment_settings", lang: "lang.payment_types", component: "payment-types"},
                    {name: "invoice_settings", lang: "lang.invoices", component: "invoice-settings"},
                    {name: "invoice_templates", lang: "lang.invoice_templates", component: "invoice-template-list"},
                    {name: "product_settings", lang: "lang.products", component: "product-settings"},
                    {name: "updates_setting", lang: "lang.updates", component: "updates-setting"},
                ],
                isVisible: function (tabName) {
                    return (this[tabName] == "1");
                },
                isSelectedTab: function (tabName) {
                    return (tabName === this.selectedTab);
                }
            }
        },
        methods: {
            selectTab: function (tabName, componentName) {
                this.selectedTab = tabName;
                this.componentName = componentName;
            },
            initSelectedTab: function () {
                var instance = this;

                this.tabs.forEach(function (tab) {
                    if (!instance.selectedTab && instance.isVisible(tab.name)) {
                        instance.selectTab(tab.name, tab.component);
                    }
                });
            }
        },
        mounted() {
            this.initSelectedTab();
            this.loadSales = this.shortCutKeyConversion();

            if (this.tab_name){
                var instance = this;
                this.tabs.forEach(function(tab) {
                    if(tab.name == instance.tab_name){
                        instance.selectTab(tab.name, tab.component);
                    }
                });
            }
        }
    }
</script>
