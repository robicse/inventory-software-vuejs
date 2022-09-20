<template>
    <div class="side-bar">
        <ul class="m-0">
            <li class="side-bar-logo">
                <a :href="publicPath+'/dashboard'">
                    <img :src="publicPath+'/uploads/logo/'+applogo" alt="">
                </a>
            </li>
            <li :class="{'active-side-bar': currentUrl == publicPath+'/dashboard'}">
                <a :href="publicPath+'/dashboard'">
                    <i class="la la-desktop"></i>
                    <div>{{ trans('lang.dashboard') }}</div>
                </a>
            </li>
            <li v-if="customers == 'manage' || customer_group == 'manage' || suppliers == 'manage' ||customers == 'read_only' || customer_group == 'read_only' || suppliers == 'read_only' "
                :class="{'active-side-bar': currentUrl == publicPath+'/contacts'}">
                <a :href="publicPath+'/contacts'">
                    <i class="la la-users"></i>
                    <div>{{ trans('lang.contacts') }}</div>
                </a>
            </li>
            <li v-if="products=='manage' || product_category == 'manage' || product_brand == 'manage' || product_group== 'manage' || variant_attributes == 'manage' || units=='manage' || products=='read_only' || product_category == 'read_only' || product_brand == 'read_only' || product_group== 'read_only' || variant_attributes == 'read_only' || units=='read_only'"
                :class="{'active-side-bar': currentUrl == publicPath+'/products'}">
                <a :href="publicPath+'/products'">
                    <i class="la la-share-alt"></i>
                    <div> {{ trans('lang.products') }}</div>
                </a>
            </li>
            <li v-if="sales==1" :class="{'active-side-bar': currentUrl == publicPath+'/sales'}">
                <a :href="publicPath+'/sales'">
                    <i class="la la-credit-card"></i>
                    <div> {{ trans('lang.sales') }}</div>
                </a>
            </li>
            <li v-if="receives==1" :class="{'active-side-bar': currentUrl == publicPath+'/receives'}">
                <a :href="publicPath+'/receives'">
                    <i class="la la-truck"></i>
                    <div> {{ trans('lang.receives') }}</div>
                </a>
            </li>
            <li v-if="sales_report == 1 || receiving_report == 1 || register_report == 1 || inventory_report == 1"
                :class="{'active-side-bar': currentUrl == publicPath+'/reports'}">
                <a :href="publicPath+'/reports'">
                    <i class="la la-pie-chart"></i>
                    <div>{{ trans('lang.reports') }}</div>
                </a>
            </li>
            <li v-if="appsettings == 1 || emailsettings == 1 || emailtemplate == 1 || tax_settings == 1 || payment_settings == 1 || sales_channels == 1 || branches_setting == 1 || roles == 1 || users == 1 || cash_register == 1 || invoice_settings == 1"
                :class="{'active-side-bar': currentUrl == publicPath+'/settings'}">
                <a :href="publicPath+'/settings'">
                    <i class="la la-gear"></i>
                    <div> {{ trans('lang.settings') }}</div>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: ['route',
            'sales',
            'suppliers',
            'receives',
            'products',
            'product_category',
            'product_brand',
            'product_group',
            'units',
            'variant_attributes',
            'sales_report',
            'receiving_report',
            'register_report',
            'inventory_report',
            'customers',
            'customer_group',
            'applogo',
            'appsettings',
            'emailsettings',
            'emailtemplate',
            'tax_settings',
            'payment_settings',
            'sales_channels',
            'branches_setting',
            'invoice_settings',
            'users',
            'roles',
            'cash_register',
        ],
        data() {
            return {
                servicePermissions: 0,
                currentUrl: '',
            }
        },
        mounted() {
            let instance = this;
            instance.currentUrl = window.location.href;
            instance.checkUrl();
        },
        methods : {
            checkUrl(){
                this.currentUrl = this.currentUrl.split("?")[0];
            }
        }
    }
</script>