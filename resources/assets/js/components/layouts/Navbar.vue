<template>
    <div>
        <!--<nav>-->
            <!--<div class="nav-wrapper">-->
                <!--<a href="/dashboard" class="brand-logo"> <img src="/images/gain.pos-logo.png" alt="" class="logo"></a>-->
                <!--<a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons bluish-text">menu</i></a>-->
                <!--<ul class="right">-->
                    <!--<li>-->
                        <!--<a href="#" @click.prevent="upCount(profile.id)" class="notification-dropdown-button" data-activates='notification-dropdown'>-->
                            <!--<i class="material-icons dp48 bluish-text">notifications_none</i>-->
                            <!--<div class="notification-badge" v-if="count!=0">{{ count }}</div>-->
                        <!--</a>-->
                    <!--</li>-->
                    <!--<li>-->

                    <!--</li>-->
                <!--</ul>-->

            <!--</div>-->
        <!--</nav>-->

        <!--&lt;!&ndash; Dropdown Structure &ndash;&gt;-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <a href="#" class="d-lg-none app-color-text" @click.prevent="showSideNavAction(true)" v-if="!showSideNav">
                <i class="la la-navicon la-2x"></i>
            </a>
            <a href="#" class="d-lg-none app-color-text" @click.prevent="showSideNavAction(false)" v-else>
                <i class="la la-close la-2x"></i>
            </a>
            <ul class="navbar-nav ml-auto">
                <!--<li class="nav-item dropdown">-->
                    <!--<a class="nav-link dropdown-toggle hide-dropdown-icon" href="#" id="navbar-notification-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @click.prevent="showSideNavAction(false)">-->
                        <!--<i class="la la-bell la-2x bluish-text"></i>-->
                    <!--</a>-->
                    <!--<div class="dropdown-menu dropdown-menu-right animated bounceInDown notification-dropdown border-0 dropdown-content" aria-labelledby="navbar-notification-dropdown">-->
                        <!--<div class="ticker"></div>-->
                        <!--<div class=''>-->
                            <!--<ul class="list-unstyled text-center">-->
                                <!--<li class="text-left p-2 mb-0">{{ trans('lang.your_notifications') }}</li>-->
                                <!--<hr class="m-0">-->
                                <!--<li class="p-3 bg-notification" v-if="items.length !== 0">-->
                                <!--&lt;!&ndash;<li v-if="items" v-for="item in items">&ndash;&gt;-->
                                    <!--&lt;!&ndash;<a @click.prevent="upNofify(item.id)" class="truncate" :class="{'unread-notification':  item.read_by.indexOf(profile.id)==-1}">{{ item.event }} sdsdsdasdas</a>&ndash;&gt;-->
                                <!--Your Notifications shows here..-->
                                <!--</li>-->
                                <!--<li class="p-3" v-else>You have no notifications</li>-->
                               <!--<hr class="m-0">-->
                                <!--<li class="p-2"><a href="#" @click.prevent="allNoti()" >{{ trans('lang.view_all') }}</a></li>-->
                            <!--</ul>-->
                        <!--</div>-->
                    <!--</div>-->
                <!--</li>-->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle hide-dropdown-icon" href="#" id="navbar-profile-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" @click.prevent="showSideNavAction(false)">
                        <img :src="appUrl+'/uploads/profile/'+profile.avatar" alt="" class="rounded-circle avatar">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right animated bounceInDown profile-dropdown border-0 p-0" aria-labelledby="navbar-profile-dropdown">
                        <div class="ticker"></div>
                        <img :src="appUrl+'/uploads/profile/'+profile.avatar" alt="" class="rounded-circle avatar-large">
                        <div class="user-name">
                            <div>{{ profile.first_name+' '+profile.last_name }}</div>
                        </div>
                        <div class="dropdown-divider m-0"></div>
                        <a :href="publicPath+'/myprofile'" class="dropdown-item d-flex align-items-center p-2">
                            <i class="la la-user la-2x pr-3"></i> {{ trans('lang.profile_title') }}
                        </a>

                        <a :href="publicPath+'/logout'" class="dropdown-item d-flex align-items-center p-2">
                            <i class="la la-sign-out la-2x pr-3"></i> {{ trans('lang.logout_nv') }}
                        </a>
                    </div>
                </li>
            </ul>
            <div class="d-lg-none mobile-app-logo">
                <a :href="publicPath+'/dashboard'">
                <img :src="appUrl+'/uploads/logo/'+applogo" alt="">
                </a>
            </div>
            <div class="side-nav" :class="{'side-nav-animate-show':showSideNav,'side-nav-animate-hide': showSideNav===false}">
                <ul>
                    <li>
                        <a :href="publicPath+'/dashboard'" class="app-color-text"> <i class="la la-desktop la-2x"></i> {{ trans('lang.dashboard') }}</a>
                    </li>
                    <li>
                        <a :href="publicPath+'/contacts'"  class="app-color-text"> <i class="la la-users la-2x"></i> {{ trans('lang.contacts') }} </a>
                    </li>
                    <li>
                        <a :href="publicPath+'/products'" class="app-color-text"> <i class="la la-share-alt la-2x"></i> {{ trans('lang.products') }}</a>
                    </li>
                    <li>
                        <a :href="publicPath+'/sales'" class="app-color-text"> <i class="la la-credit-card la-2x"></i> {{ trans('lang.sales') }}</a>
                    </li>
                    <li>
                        <a :href="publicPath+'/receives'" class="app-color-text"><i class="la la-truck la-2x"></i> {{ trans('lang.receives') }}</a>
                    </li>
                    <li>
                        <a :href="publicPath+'/reports'" class="app-color-text"> <i class="la la-pie-chart la-2x"></i> {{ trans('lang.reports') }} </a>
                    </li>
                    <li>
                        <a  :href="publicPath+'/settings'"  class="app-color-text"> <i class="la la-gear la-2x"></i> {{ trans('lang.settings') }}</a>
                    </li>
                </ul>
            </div>
            <div class="side-nav-close" :class="{'d-none':!showSideNav}" @click.prevent="showSideNavAction(false)"></div>
        </nav>
    </div>
</template>
<script>
    export default {
        props: ["profile","applogo"],
        data() {
            return {

                first_name: '',
                last_name: '',
                avatar: '',
                id:'',
                read_by:[],
                items: [],
                count: [],
                showSideNav:'',
            }
        },
        mounted(){
            this.readNotifi();
            this.notifyCount();
        },

        methods: {
            showSideNavAction(value)
            {
                if (value===false && this.showSideNav=='')
                {
                    this.showSideNav = '';
                }
                else {
                    this.showSideNav = value
                }
            },
            readNotifi(){
                axios.get(this.appUrl+'/notifications')
                    .then(response => {
                    this.items = response.data;
                });
            },
            upNofify(id){
                axios.post(this.appUrl+'/upnotify/'+id, {
                    read_by: this.profile.id
                })
                    .then(response => {
                    });
                location.href = this.appUrl+"/booking/"+id;

            },
            notifyCount(){
                axios.get(this.appUrl+'/count')
                    .then(response => {

                        this.count = response.data;
                    });
            },
            upCount(id){
                axios.post(this.appUrl+'/countup/'+id, {
                    read_by: this.profile.id
                })
                    .then(response => {
                    });
                this.count =0;
            },
            allNoti()
            {
                location.href = this.appUrl+"/notifications";
            }
        }
    }

</script>
