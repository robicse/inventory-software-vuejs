<!--suppress ALL -->
<template>
    <div class="main-layout-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent m-0">
                <li class="breadcrumb-item" v-if="order_type=='sales'">
                    <a href="#" data-toggle="modal" :class="{disabled:cart.length != 0}"
                       data-target="#sales-or-return-type-select-modal">
                        {{ capitalizeFirstLetter(salesOrReturnType) }}
                    </a>
                </li>
                <li class="breadcrumb-item" v-else>
                    <span>
                        {{trans('lang.receives')}}
                    </span>
                </li>
                <li class="breadcrumb-item" v-if="order_type=='receiving'">
                    <span>
                        <a href="#" data-toggle="modal" data-target="#sales-or-receiving-type-select-modal"
                           :class="{disabled:cart.length != 0}">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                        </a>
                    </span>
                </li>
                <li class="breadcrumb-item" v-if="order_type=='sales'">
                    <a href="#" data-toggle="modal" data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0}" v-if="salesOrReceivingType=='customer'">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                    </a>
                    <a href="#" data-toggle="modal" data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0}" v-else>
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                    </a>
                </li>
                <li class="breadcrumb-item" v-if="selectedBranchID && total_branch>1">
                    <a href="#" @click.prevent="branchModalAction()">
                        {{ selectedBranchName }}
                    </a>
                </li>

                <li class="breadcrumb-item active" aria-current="page"
                    v-if="selectedCashRegisterID && selectedBranchID==selectedCashRegisterBranchID">
                    <a href="#" @click.prevent="cashRegisterModalAction()">
                        {{ selectedCashRegisterName }}
                    </a>
                </li>
            </ol>
        </nav>
        <div class="d-flex" style="height: calc(100vh - 6rem);">
            <div style="flex: 1 0;">
                <div class="main-layout-card mb-3">
                    <div class="main-layout-card-content p-2">
                        <div class="row mx-0">
                            <div class="col-12 p-0">
                                <!--Product search starts-->
                                <div class="input-group"
                                     v-if="salesOrReturnType == 'sales' || order_type =='receiving'">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input id="search" type="text" class="form-control pr-4 rounded-right"
                                           :placeholder="trans('lang.search_product')" aria-label="Search"
                                           aria-describedby="search"
                                           v-model="productSearchValue" @keyup="searchProductInput"
                                           v-shortkey="productSearch"
                                           @shortkey="commonMethodForAccessingShortcut('productSearch')" ref="search">
                                    <div v-if="productSearchValue">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="productSearchValue='',getProductData()">
                                        </i>
                                    </div>
                                </div>
                                <div class="input-group" v-else>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control pr-4 rounded-right"
                                           v-model="orderSearchValue"
                                           :placeholder="trans('lang.search_orders')" aria-label="Search"
                                           aria-describedby="search" @input="searchOrderInput">
                                    <div v-if="orderSearchValue!=''">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="orderSearchValue=''"></i>
                                    </div>
                                    <!-- order search result dropdown structure starts-->
                                    <div class="dropdown-menu dropdown-menu-right w-100"
                                         :class="{'show':orderSearchValue}">
                                        <pre-loader v-if="!hideOrderSearchPreLoader"
                                                    class="small-loader-container"></pre-loader>
                                        <div class="px-3 py-1 text-center"
                                             v-else-if="hideOrderSearchPreLoader && orders.length === 0">
                                            {{trans('lang.no_result_found')}}
                                        </div>
                                        <div class="customers-container" v-else-if="orders.length !== 0">
                                            <span v-for=" (order,index) in orders" @click.prevent="selectOrder(order)">
                                                <a href="#" class="dropdown-item">
                                                <h6 class="m-0"> {{trans('lang.invoice_id')}} : {{order.invoice_id}}
                                                    <br>
                                                    <small>{{trans('lang.date')}} : {{ dateFormats(order.date) }} {{ dateFormatsWithTime(order.date) }}</small>
                                                </h6>
                                                </a>
                                                <div class="dropdown-divider m-0"></div>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- order search result dropdown structure ends-->
                                </div>
                                <!--Product search ends-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--Products result starts-->
                <pre-loader
                        v-if="!hideProductSearchPreLoader && !(order_type==='sales' && salesOrReturnType==='returns')">
                </pre-loader>
                <div class="row all-products" v-else>
                    <div v-if="salesOrReturnType == 'sales' || order_type =='receiving'"
                         class="col-6 col-md-4 col-lg-4 col-xl-3 pr-0 mb-3 standard-product"
                         v-for="product in products">
                        <a href="#" class="app-color-text" data-toggle="modal"
                           :data-target="variantProductCard(product.variants)"
                           @click.prevent="productCardAction(product)">
                            <div class="product-card bg-white rounded ">
                                <div class="product-img-container image-property"
                                     :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + product.productImage + ')' }">
                                </div>
                                <div class="product-card-content product-content ">
                                    <div v-if="product.variants.length==1" class="position-relative h-100">
                                        <div v-for="variant in product.variants"
                                             :class="{ 'h-100': variant.attribute_values=='default_variant' || product.variants.length>1}">
                                            <div v-if="variant.attribute_values!='default_variant'"
                                                 :class="{ 'h-100': variant.attribute_values=='default_variant' || product.variants.length>1}">
                                                <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                    {{ product.title }}<br> {{'(' + variant.variant_title + ')'}}
                                                </div>
                                            </div>
                                            <div v-else class="h-100">
                                                <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                    <span class="limit"> {{ product.title }} </span>
                                                </div>
                                            </div>
                                            <div class="product-card-font position-absolute rounded-right app-color text-white product-price"
                                                 style=" top: -3rem; padding: 2px 4px; left: 0;">
                                                {{ numberFormat(variant.price) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="position-relative h-100">
                                        <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center ">
                                            <span class="limit"> {{ product.title }} </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!--Show in mobile screen-->
                    <div id="cartShow" v-show='toggleCart'>
                        <a href="#" data-toggle="modal" data-target="#myModal"><i class="la la-shopping-cart"></i></a>
                    </div>
                    <!-- Modal -->
                    <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><i class="la la-close"></i>
                                    </button>
                                </div>
                                <div class="modal-body" id="cartBody">
                                    <div style="position: relative;width: 100%;margin-top: -20px;margin-bottom: -15px;">
                                        <div class="main-layout-card"
                                             style="min-height: 600px;">
                                            <div id="cart-section-1" class="modal-layout-header p-0">
                                                <div class="sales-search p-2" v-show="customerNotAdded">
                                                    <div class="row">
                                                        <div :class="{'col-10': addcustomer=='manage','col-12':addcustomer!='manage'}"
                                                             class="pr-0">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="la la-user"></i></span>
                                                                </div>
                                                                <input type="text"
                                                                       class="form-control pr-4 rounded-right"
                                                                       v-if="order_type=='sales'"
                                                                       v-model="customerSearchValue"
                                                                       :placeholder="trans('lang.search_customers')"
                                                                       aria-label="Search"
                                                                       aria-describedby="search"
                                                                       @input="searchCustomerInput"
                                                                       @keydown.enter="selectCustomer(customers[highlightIndex])"
                                                                       @keydown.down="down"
                                                                       @keydown.up="up">
                                                                <input type="text"
                                                                       class="form-control pr-4 rounded-right"
                                                                       v-else
                                                                       v-model="customerSearchValue"
                                                                       :placeholder="trans('lang.search_suppliers')"
                                                                       aria-label="Search"
                                                                       aria-describedby="search"
                                                                       @input="searchCustomerInput">
                                                                <div v-if="customerSearchValue!=''">
                                                                    <i class="la la-close position-absolute p-1 customer-search-cancel"
                                                                       @click.prevent="customerSearchValue=''"></i>
                                                                </div>
                                                                <!-- Customer search result dropdown structure starts-->
                                                                <div class="dropdown-menu dropdown-menu-right w-100"
                                                                     :class="{'show':customerSearchValue}">
                                                                    <pre-loader v-if="!hideCustomerSearchPreLoader"
                                                                                class="small-loader-container"></pre-loader>
                                                                    <div class="px-3 py-1 text-center"
                                                                         v-else-if="hideCustomerSearchPreLoader && customers.length === 0">
                                                                        {{trans('lang.no_result_found')}}
                                                                    </div>
                                                                    <div class="customers-container"
                                                                         v-else-if="customers.length !== 0">
                                                <span v-for="(customer,index) in customers">
                                                    <a href="#"
                                                       class="dropdown-item"
                                                       :class="{ active: index === highlightIndex }"
                                                       @click.prevent="selectCustomer(customer)">
                                                        <h6 class="m-0">
                                                            {{ customer.first_name + ' ' + customer.last_name}}
                                                            <br>
                                                            <small> {{customer.email}}</small>
                                                        </h6>
                                                    </a>
                                                <div class="dropdown-divider m-0"></div>
                                                </span>
                                                                    </div>
                                                                </div>
                                                                <!-- Customer search result dropdown structure ends-->
                                                            </div>
                                                        </div>
                                                        <div class="col-2" v-if="addcustomer=='manage'">
                                     <span v-if="order_type=='sales'">
                                    <a class="btn app-color" style="padding: 7px 22px 7px 20px !important;"
                                       data-toggle="modal" data-target="#customer-add-edit-modal"
                                       href="#" @click.prevent="isCustomerModalActive=true"
                                       v-shortkey="addCustomerShortKey"
                                       @shortkey="commonMethodForAccessingShortcut('addCustomer')">
                                        <i class="la la-user-plus"></i>
                                    </a>
                                     </span>
                                                            <span v-else>
                                    <a class="btn app-color" data-toggle="modal" data-target="#supplier-add-edit-modal"
                                       href="#" @click.prevent="isCustomerModalActive=true">
                                        <i class="la la-user-plus"></i>
                                    </a>
                                     </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="p-2" v-if="!customerNotAdded"
                                                     v-for=" (customer,index) in selectedCustomer">
                                                    <h6 class="m-0 cart-product-details-parent">
                                                        <div class="cart-product-details-child">
                                                            {{ customer.first_name + ' ' + customer.last_name}}
                                                            <br>
                                                            <small> {{customer.email}}</small>
                                                        </div>
                                                        <a href="#" class="cart-product-details-child text-right"
                                                           @click.prevent="removeSelectedCustomer(index)">
                                                            <i class="la la-close"></i>
                                                        </a>
                                                    </h6>
                                                </div>
                                            </div>
                                            <div id="cart-section-2" class="cart-items-wrapper"
                                                 :class="{'h-100 d-flex align-items-center justify-content-center' : cart.length==0}">
                        <span v-if="cart.length==0">
                            {{trans('lang.empty_cart')}}
                        </span>
                                                <div v-else class="cart-item-container py-1"
                                                     v-for="(cartItem,index) in cart"
                                                     :class="{'active-cart-item': cartItem.showItemCollapse }">
                                                    <div class="form-row mx-0 px-1 cart-item">
                                                        <!--<div class="col s7 no-padding">-->
                                                        <div class="col-6 p-0 cart-item-btn"
                                                             @click.prevent="cartItemCollapse(index,cartItem.variantID)">
                                                            <div class="row mx-0 my-auto">
                                                                <div class="col-1 p-0 m-auto">
                                                                    <i class="la la-chevron-circle-right la-2x cart-icon"
                                                                       :class="{'cart-icon-rotate':cartItem.showItemCollapse}"></i>
                                                                </div>
                                                                <div class="col-11  my-auto mx-0 pr-0">
                                                                    <div class="row mx-0 cart-item-title"
                                                                         :class="{cartProduct: cartItem.productID == activeProductId && cartItem.variantID == activeVariantId && cartItem.orderType !== 'discount'}">
                                                                        <div class="col-12 pl-1 p-0 my-auto mx-0">
                                                                            {{ cartItem.productTitle }}
                                                                            <br>
                                                                            <span v-if="cartItem.productTitle != cartItem.variantTitle && cartItem.variantTitle != 'default_variant' && cartItem.orderType === ''">( {{ cartItem.variantTitle }} )</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--<div class="col s4 no-padding">-->
                                                        <div class="col-3 my-auto px-0 ml-3 mr-0">
                                                            <div class="d-flex justify-content-between mr-1 cart-quantity">
                                                                <div class="pl-0 mx-0">
                                                                    <a href="#"
                                                                       :class="{disabled:cartItem.orderType ==='discount' || (salesOrReturnType==='returns' && order_type === 'sales')}"
                                                                       @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'-')">
                                                                        <i class="la la-minus-circle la-2x cart-icon-color"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="align-self-center">
                                                                    {{ cartItem.quantity }}
                                                                </div>
                                                                <div class="">
                                                                    <a href="#"
                                                                       :class="{disabled:cartItem.orderType ==='discount'}"
                                                                       @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'+')">
                                                                        <i class="la la-plus-circle la-2x cart-icon-color"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-3 d-flex pr-0 pl-0 cart-calculatedPrice">
                                                            <div class="align-self-center ml-auto">
                                                                <span>{{ numberFormat(cartItem.calculatedPrice) }}</span>
                                                            </div>
                                                            <div class="align-self-center ml-2">
                                                                <a href="#" class="red-text "
                                                                   @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'delete')"><i
                                                                        class="la la-trash del-icon-color"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-row mx-0 px-2  collapse-animation"
                                                         :class="{'collapsed':cartItem.showItemCollapse}">
                                                        <div class="form-group pl-0 mb-zero"
                                                             :class="[manage_price == 1? 'col-4':'col-6']">
                                                            <label :for="'cart-item-quantity'+index"
                                                                   class="label-in-cart ">
                                                                {{trans('lang.quantity')}}</label>
                                                            <input type="text" :id="'cart-item-quantity'+index"
                                                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode >= 48 && event.charCode <= 57"
                                                                   @keyup="setCartItemsToCookieOrDB(1)"
                                                                   v-model="cartItem.quantity"
                                                                   class="form-control">
                                                        </div>
                                                        <div class="form-group pr-0 mb-zero"
                                                             :class="[manage_price == 1? 'col-4':'col-6']"
                                                             v-if="order_type=='receiving' || manage_price == 1 ">
                                                            <label :for="'cart-item-price'+index"
                                                                   class="label-in-cart">{{trans('lang.price')}}</label>
                                                            <payment-input :id="'cart-item-price'+index"
                                                                           :inputValue="cartItem.unformPrice"
                                                                           :index="index"
                                                                           @input="setPropductNewPrice"></payment-input>
                                                        </div>
                                                        <div class="form-group pr-0 mb-zero"
                                                             :class="[manage_price == 1? 'col-4':'col-6']"
                                                             v-if="order_type=='sales'">
                                                            <label :for="'cart-item-discount'+index"
                                                                   class="label-in-cart w-100">
                                                                {{trans('lang.item_discount')}} (%)
                                                            </label>
                                                            <div class="position-relative">
                                                                <payment-input :id="'cart-item-discount'+index"
                                                                               v-model="cartItem.discount"
                                                                               :inputValue="discount"
                                                                               @input="setCartItemsToCookieOrDB(1)"></payment-input>
                                                                <div v-if="cartItem.discount">
                                                                    <i class="la la-close position-absolute p-1 customer-search-cancel"
                                                                       @click.prevent="cartItem.discount=null,subTotalAmount()"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 p-0 pb-2">
                                                            <label :for="'cart-item-note'+index"
                                                                   class="label-in-cart">{{trans('lang.note')}}</label>
                                                            <textarea :id="'cart-item-note'+index"
                                                                      @keyup="setCartItemsToCookieOrDB(1)"
                                                                      class="form-control"
                                                                      v-model="cartItem.cartItemNote"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="cart-section-3"
                                                 class="position-absolute fixed-bottom product-card-font">
                                                <div class="row mx-0 px-3 py-2 font-weight-bold border-top border-bottom">
                                                    <div class="col-6 p-0">
                                                        {{trans('lang.sub_total')}}
                                                    </div>
                                                    <div class="col-6 p-0 text-right">
                                                        {{ numberFormat(total) }}
                                                    </div>
                                                </div>
                                                <div class="row mx-0 px-3 py-2  border-bottom">
                                                    <div class="col-6 p-0">
                                                        Vat
                                                    </div>
                                                    <div class="col-6 p-0 text-right">
                                                        {{ numberFormat(tax) }}
                                                    </div>
                                                </div>
                                                <div v-if="salesOrReturnType==='sales'||order_type==='receiving'"
                                                     class="row mx-0 px-3 py-2  border-bottom" id="pop_mouse1"
                                                     v-popover:foo.right>
                                                    <div class="col-6 p-0">
                                                        {{trans('lang.discount_all_items_by_percent')}}
                                                        <popover name="foo">
                                                            <payment-input :inputValue="newdiscount"
                                                                           @input="allProductDiscount"
                                                                           :discountField="discountOnAllItem"></payment-input>
                                                        </popover>
                                                    </div>
                                                    <div class="col-3 p-0 text-center">
                                                        <div class="btn-group dropright" role="group">
                                                        </div>
                                                    </div>
                                                    <div class="col-3 p-0 text-right">
                                <span v-if="discount">
                                    <i class="la la-edit myicon1"></i>{{ decimalFormat(discount) +'%'}}
                                </span>
                                                        <!--data-toggle="popover"-->
                                                        <span v-else>
                                     <i class="la la-edit myicon1"></i>{{ decimalFormat('0.00')+'%' }}
                                </span>
                                                    </div>
                                                </div>
                                                <div class="row mx-0 px-3 py-2  border-bottom" id="pop_mouse2"
                                                     v-popover:foo1.right>
                                                    <div class="col-6 p-0">
                                                        {{trans('lang.discount_entire_sale')}}
                                                    </div>
                                                    <div class="col-3 p-0 text-center">
                                                        <div class="btn-group dropright" role="group">
                                                            <popover name="foo1">
                                                                <payment-input :inputValue="newOverAllDiscount"
                                                                               :prodictTotalWithoutDiscount="prodictTotalWithoutDiscount"
                                                                               @input="addOverAllDiscount"></payment-input>
                                                            </popover>
                                                        </div>
                                                    </div>
                                                    <div class="col-3 p-0 text-right">
                                <span v-if="overAllDiscount">
                                     <i class="la la-edit myicon2"></i>{{decimalFormat(overAllDiscount)}}
                                </span>
                                                        <span v-else>
                                      <i class="la la-edit myicon2"></i>{{decimalFormat('0.00')}}
                                </span>
                                                    </div>
                                                </div>
                                                <div class="row mx-0 px-3 py-2 font-weight-bold  border-bottom">
                                                    <div class="col-6 p-0">
                                                        {{trans('lang.total')}}
                                                    </div>
                                                    <div class="col-6 p-0 text-right">
                                                        {{ numberFormat(grandTotal) }}
                                                    </div>
                                                </div>
                                                <div class="p-3 border-bottom">
                                                    <button data-dismiss="modal" class="btn pay-btn app-color"
                                                            data-toggle="modal" data-target="#cart-payment-modal"
                                                            href="#" @click.prevent="cartSave()"
                                                            :disabled="cart.length==0"
                                                            v-shortkey="payment"
                                                            @shortkey="commonMethodForAccessingShortcut('pay')">
                                                        {{trans('lang.pay')}}
                                                    </button>
                                                </div>
                                                <div v-if="order_type=='sales'" class="row mx-0">
                                                    <a href="#" data-toggle="modal" data-target="#hold-orders-modal"
                                                       class="col-4 p-0 text-center border-right hold-items"
                                                       :class="{'disabled':orderHoldItems.length == 0 ||cart.length != 0 || salesOrReturnType == 'returns' }"
                                                       @click.prevent="">
                                                        <i class="la la-recycle la-2x p-2 app-color-text"></i><span
                                                            class="badge badge-danger hold-items-badge-position"
                                                            v-if="orderHoldItems.length>0">{{orderHoldItems.length}}</span>
                                                    </a>
                                                    <a href="#" data-toggle="modal"
                                                       class="col-4 p-0 text-center border-right hold-cart"
                                                       :class="{'disabled':cart.length==0}" @click.prevent="orderHold()"
                                                       v-shortkey="holdCarditem"
                                                       @shortkey="commonMethodForAccessingShortcut('holdCard')">
                                                        <i class="la la-pause la-2x p-2 text-warning"></i>
                                                    </a>

                                                    <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                                                       class="col-4 p-0 text-center clear-cart"
                                                       :class="{'disabled':cart.length==0}"
                                                       @click.prevent="" v-shortkey="cancelCarditem"
                                                       @shortkey="commonMethodForAccessingShortcut('cancelCarditem')">
                                                        <i class="la la-trash la-2x p-2 text-danger"></i>
                                                    </a>
                                                </div>
                                                <div v-else class="row mx-0 receiveDeleteButton">
                                                    <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                                                       class="col-12 p-0 text-center clear-cart"
                                                       :class="{'disabled':cart.length==0}"
                                                       @click.prevent="">
                                                        <i class="la la-trash la-2x p-2 text-danger"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!--End show in mobile screen-->
                    <div class="col-12"
                         v-if="products.length==0 && !(order_type==='sales'&&salesOrReturnType==='returns')">
                        <div class="main-layout-card">
                            <div class="main-layout-card-content text-center">
                                {{trans('lang.no_result_found')}}
                            </div>
                        </div>
                    </div>
                </div>
                <!--Products result ends-->
            </div>
            <div id="layoutTop" style="position: relative; width: 30rem;">
                <div class="main-layout-card"
                     style=" position: absolute; top: 0; bottom: 0; left: 16px; right: 0;  min-height: 450px;">
                    <div id="cart-section-1" class="modal-layout-header p-0">
                        <div v-if="isSelectedBranch" class="sales-search p-2" v-show="customerNotAdded">
                            <div class="row">
                                <div :class="{'col-10': addcustomer=='manage','col-12':(addcustomer!='manage'|| salesOrReceivingType == 'internal')}"
                                     class="pr-0">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="la la-user"></i></span>
                                        </div>
                                        <input type="text"
                                               class="form-control pr-4 rounded-right"
                                               v-if="order_type=='sales' && salesOrReceivingType != 'internal' "
                                               v-model="customerSearchValue"
                                               :placeholder="trans('lang.search_customers')"
                                               aria-label="Search"
                                               aria-describedby="search"
                                               @input="searchCustomerInput"
                                               @keydown.enter="selectCustomer(customers[highlightIndex])"
                                               @keydown.down="down"
                                               @keydown.up="up">
                                        <input type="text"
                                               class="form-control pr-4 rounded-right"
                                               v-else-if="order_type=='sales' || order_type=='receiving' && salesOrReceivingType == 'internal'"
                                               v-model="branchSearchValue"
                                               :placeholder="trans('lang.search_branch')"
                                               aria-label="Search"
                                               aria-describedby="search"
                                               @input="searchBranchInput"
                                               @keydown.enter="selectSearchBranch(branches[highlightIndex])"
                                               @keydown.down="down"
                                               @keydown.up="up">
                                        <input type="text"
                                               class="form-control pr-4 rounded-right"
                                               v-else
                                               v-model="customerSearchValue"
                                               :placeholder="trans('lang.search_suppliers')"
                                               aria-label="Search"
                                               aria-describedby="search"
                                               @input="searchCustomerInput"
                                               @keydown.enter="selectCustomer(customers[highlightIndex])"
                                               @keydown.down="down"
                                               @keydown.up="up">
                                        <div v-if="customerSearchValue!=''">
                                            <i class="la la-close position-absolute p-1 customer-search-cancel"
                                               @click.prevent="customerSearchValue=''"></i>
                                        </div>
                                        <div v-if="branchSearchValue!=''">
                                            <i class="la la-close position-absolute p-1 customer-search-cancel"
                                               @click.prevent="branchSearchValue=''"></i>
                                        </div>
                                        <!-- Customer search result dropdown structure starts-->
                                        <div class="dropdown-menu dropdown-menu-right w-100"
                                             :class="{'show':customerSearchValue}">
                                            <pre-loader v-if="!hideCustomerSearchPreLoader"
                                                        class="small-loader-container"></pre-loader>
                                            <div class="px-3 py-1 text-center"
                                                 v-else-if="hideCustomerSearchPreLoader && customers.length === 0">
                                                {{trans('lang.no_result_found')}}
                                            </div>
                                            <div class="customers-container" v-else-if="customers.length !== 0">
                                                <span v-for="(customer,index) in customers">
                                                    <a href="#"
                                                       class="dropdown-item"
                                                       :class="{ active: index === highlightIndex }"
                                                       @click.prevent="selectCustomer(customer)">
                                                        <h6 class="m-0">
                                                            {{ customer.first_name + ' ' + customer.last_name}}
                                                            <br>
                                                            <small> {{customer.email}}</small>
                                                        </h6>
                                                    </a>
                                                <div class="dropdown-divider m-0"></div>
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Customer search result dropdown structure ends-->
                                        <!--Branches search result dropdown structure starts-->
                                        <div class="dropdown-menu dropdown-menu-right w-100"
                                             :class="{'show':branchSearchValue}">
                                            <pre-loader v-if="!hideCustomerSearchPreLoader"
                                                        class="small-loader-container"></pre-loader>
                                            <div class="px-3 py-1 text-center"
                                                 v-else-if="hideCustomerSearchPreLoader && branches.length === 0">
                                                {{trans('lang.no_result_found')}}
                                            </div>
                                            <div class="customers-container" v-else-if="branches.length !== 0">
                                                <span v-for="(branch,index) in branches">
                                                    <a href="#"
                                                       class="dropdown-item"
                                                       :class="{ active: index === highlightIndex }"
                                                       @click.prevent="selectSearchBranch(branch)">
                                                        <h6 class="m-0">
                                                            {{ branch.name }}
                                                        </h6>
                                                    </a>
                                                <div class="dropdown-divider m-0"></div>
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Branches search result dropdown structure ends-->
                                    </div>
                                </div>
                                <!--Suplier Add button-->
                                <div class="col-2"
                                     v-if="addcustomer=='manage' && salesOrReceivingType != 'internal' && order_type=='receiving'">
                                    <span>
                                    <a class="btn app-color" data-toggle="modal" data-target="#supplier-add-edit-modal"
                                       href="#" @click.prevent="isCustomerModalActive=true">
                                        <i class="la la-user-plus"></i>
                                    </a>
                                     </span>
                                </div>
                                <!--Customer add button-->
                                <div class="col-2"
                                     v-if="addcustomer=='manage' && salesOrReceivingType != 'internal' && order_type=='sales'">
                                    <span>
                                    <a class="btn app-color" data-toggle="modal" data-target="#customer-add-edit-modal"
                                       href="#" @click.prevent="isCustomerModalActive=true">
                                        <i class="la la-user-plus"></i>
                                    </a>
                                     </span>
                                </div>
                            </div>
                        </div>
                        <div class="p-2" v-if="!customerNotAdded" v-for=" (customer,index) in selectedCustomer">
                            <h6 class="m-0 cart-product-details-parent">
                                <div class="cart-product-details-child">
                                    {{ customer.first_name + ' ' + customer.last_name}}
                                    <br>
                                    <small> {{customer.email}}</small>
                                </div>
                                <a href="#" class="cart-product-details-child text-right"
                                   @click.prevent="removeSelectedCustomer(index)">
                                    <i class="la la-close"></i>
                                </a>
                            </h6>
                        </div>
                        <div class="p-2" v-if="salesOrReceivingType == 'internal' && selectedSearchBranch != 0">
                            <h6 class="m-0 cart-product-details-parent">
                                <div class="cart-product-details-child">
                                    {{ selectedSearchBranch.name }}
                                </div>
                                <a href="#" class="cart-product-details-child text-right"
                                   @click.prevent="removeSelectedBranch">
                                    <i class="la la-close"></i>
                                </a>
                            </h6>
                        </div>
                    </div>
                    <div id="cart-section-2" class="cart-items-wrapper"
                         :class="{'h-100 d-flex align-items-center justify-content-center' : cart.length==0}">
                        <span v-if="cart.length==0">
                            {{trans('lang.empty_cart')}}
                        </span>
                        <div v-else class="cart-item-container py-1" v-for="(cartItem,index) in cart"
                             :class="{'active-cart-item': cartItem.showItemCollapse }">
                            <div class="form-row mx-0 px-1 cart-item">
                                <!--<div class="col s7 no-padding">-->
                                <div class="col-6 p-0 cart-item-btn"
                                     @click.prevent="cartItemCollapse(index,cartItem.variantID)">
                                    <div class="row mx-0 my-auto">
                                        <div class="col-1 p-0 m-auto">
                                            <i class="la la-chevron-circle-right la-2x cart-icon"
                                               :class="{'cart-icon-rotate':cartItem.showItemCollapse}"></i>
                                        </div>
                                        <div class="col-11  my-auto mx-0 pr-0">
                                            <div class="row mx-0 cart-item-title"
                                                 :class="{cartProduct: cartItem.productID == activeProductId && cartItem.variantID == activeVariantId && cartItem.orderType !== 'discount'}">
                                                <div class="col-12 pl-1 p-0 my-auto mx-0">
                                                    {{ cartItem.productTitle }}
                                                    <br>
                                                    <span v-if="cartItem.productTitle != cartItem.variantTitle && cartItem.variantTitle != 'default_variant' && cartItem.orderType === ''">( {{ cartItem.variantTitle }} )</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="col s4 no-padding">-->
                                <div class="col-3 my-auto px-0 ml-3 mr-0">
                                    <div class="d-flex justify-content-between mr-1 cart-quantity">
                                        <div class="pl-0 mx-0">
                                            <a href="#"
                                               :class="{disabled:cartItem.orderType ==='discount' || (salesOrReturnType==='returns' && order_type === 'sales')}"
                                               @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'-')">
                                                <i class="la la-minus-circle la-2x cart-icon-color"></i>
                                            </a>
                                        </div>
                                        <div class="align-self-center">
                                            {{ decimalFormat(cartItem.quantity) }}
                                        </div>
                                        <div class="">
                                            <a href="#" :class="{disabled:cartItem.orderType ==='discount'}"
                                               @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'+')">
                                                <i class="la la-plus-circle la-2x cart-icon-color"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3 d-flex pr-0 pl-0 cart-calculatedPrice">
                                    <div class="align-self-center ml-auto">
                                        <span>{{ numberFormat(cartItem.calculatedPrice) }}</span>
                                    </div>
                                    <div class="align-self-center ml-2">
                                        <a href="#" class="red-text "
                                           @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'delete')"><i
                                                class="la la-trash del-icon-color"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row mx-0 px-2  collapse-animation"
                                 :class="{'collapsed':cartItem.showItemCollapse}">
                                <div class="form-group pl-0 mb-zero" :class="[manage_price == 1? 'col-4':'col-6']">
                                    <label :for="'cart-item-quantity'+index" class="label-in-cart ">
                                        {{trans('lang.quantity')}}</label>
                                    <payment-input :id="'cart-item-quantity'+index"
                                                   :inputValue="decimalFormat(cartItem.quantity)" :index="index"
                                                   @input="setQuantityInCart">
                                    </payment-input>
                                </div>
                                <div class="form-group pr-0 mb-zero" :class="[manage_price == 1? 'col-4':'col-6']"
                                     v-if="order_type=='receiving' || manage_price == 1 ">
                                    <label :for="'cart-item-price'+index"
                                           class="label-in-cart">{{trans('lang.price')}}</label>
                                    <payment-input :id="'cart-item-price'+index"
                                                   :inputValue="cartItem.unformPrice" :index="index"
                                                   @input="setPropductNewPrice"></payment-input>
                                </div>
                                <div class="form-group pr-0 mb-zero" :class="[manage_price == 1? 'col-4':'col-6']"
                                     v-if="order_type=='sales'">
                                    <label :for="'cart-item-discount'+index" class="label-in-cart w-100">
                                        {{trans('lang.item_discount')}} (%)
                                    </label>
                                    <div class="position-relative">
                                        <payment-input :id="'cart-item-discount'+index" v-model="cartItem.discount"
                                                       :inputValue="discount"
                                                       @input="setCartItemsToCookieOrDB(1)"></payment-input>
                                        <!--                                        <div v-if="cartItem.discount">-->
                                        <!--                                            <i class="la la-close position-absolute p-1 customer-search-cancel"-->
                                        <!--                                               @click.prevent="cartItem.discount=null, subTotalAmount()"></i>-->
                                        <!--                                        </div>-->
                                    </div>
                                </div>
                                <div class="col-12 p-0 pb-2">
                                    <label :for="'cart-item-note'+index"
                                           class="label-in-cart">{{trans('lang.note')}}</label>
                                    <textarea :id="'cart-item-note'+index" @keyup="setCartItemsToCookieOrDB(1)"
                                              class="form-control"
                                              v-model="cartItem.cartItemNote"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="cart-section-3" class="position-absolute fixed-bottom product-card-font">
                        <div class="row mx-0 px-3 py-2 font-weight-bold border-top border-bottom">
                            <div class="col-6 p-0">
                                {{trans('lang.sub_total')}}
                            </div>
                            <div class="col-6 p-0 text-right">
                                {{ numberFormat(subTotal) }}
                            </div>
                        </div>
                        <div class="row mx-0 px-3 py-2  border-bottom">
                            <div class="col-6 p-0">
                                Vat
                            </div>
                            <div class="col-6 p-0 text-right">
                                {{ numberFormat(tax) }}
                            </div>
                        </div>
                        <div v-if="salesOrReturnType==='sales'||order_type==='receiving'"
                             class="row mx-0 px-3 py-2  border-bottom" id="pop_mouse1" v-popover:foo.right>
                            <div class="col-6 p-0">
                                {{trans('lang.discount_all_items_by_percent')}}
                                <popover name="foo">
                                    <payment-input :inputValue="newdiscount" @input="allProductDiscount"
                                                   :discountField="discountOnAllItem"></payment-input>
                                </popover>
                            </div>
                            <div class="col-3 p-0 text-center">
                                <div class="btn-group dropright" role="group">
                                </div>
                            </div>
                            <div class="col-3 p-0 text-right">
                                <span v-if="discount">
                                    <i class="la la-edit myicon1"></i>{{ decimalFormat(discount) +'%'}}
                                </span>
                                <!--data-toggle="popover"-->
                                <span v-else>
                                     <i class="la la-edit myicon1"></i>{{ decimalFormat('0.00')+'%' }}
                                </span>
                            </div>
                        </div>
                        <div class="row mx-0 px-3 py-2  border-bottom" id="pop_mouse2" v-popover:foo1.right>
                            <div class="col-6 p-0">
                                {{trans('lang.discount_entire_sale')}}
                            </div>
                            <div class="col-3 p-0 text-center">
                                <div class="btn-group dropright" role="group">
                                    <popover name="foo1">
                                        <payment-input :inputValue="newOverAllDiscount"
                                                       :prodictTotalWithoutDiscount="prodictTotalWithoutDiscount"
                                                       @input="addOverAllDiscount"></payment-input>
                                    </popover>
                                </div>
                            </div>
                            <div class="col-3 p-0 text-right">
                                <span v-if="overAllDiscount">
                                     <i class="la la-edit myicon2"></i>{{numberFormat(newOverAllDiscount)}}
                                </span>
                                <span v-else>
                                      <i class="la la-edit myicon2"></i>{{decimalFormat('0.00')}}
                                </span>
                            </div>
                        </div>
                        <div class="row mx-0 px-3 py-2 font-weight-bold  border-bottom">
                            <div class="col-6 p-0">
                                {{trans('lang.total')}}
                            </div>
                            <div class="col-6 p-0 text-right">
                                {{ numberFormat(grandTotal) }}
                            </div>
                        </div>
                        <div class="p-3 border-bottom" v-if="salesOrReceivingType == 'internal'">
                            <button class="btn pay-btn app-color" data-toggle="modal" data-target="#cart-payment-modal"
                                    href="#" @click.prevent="cartSave()"
                                    :disabled="selectedSearchBranch == 0 || enableDisablePay()"
                                    v-shortkey="payment"
                                    @shortkey="commonMethodForAccessingShortcut('pay')">
                                {{trans('lang.pay')}}
                            </button>
                        </div>
                        <div v-else class="p-3 border-bottom">
                            <button class="btn pay-btn app-color" data-toggle="modal" data-target="#cart-payment-modal"
                                    href="#" @click.prevent="cartSave()" :disabled="enableDisablePay()"
                                    v-shortkey="payment"
                                    @shortkey="commonMethodForAccessingShortcut('pay')">
                                {{trans('lang.pay')}}
                            </button>
                        </div>
                        <div v-if="order_type=='sales'" class="row mx-0">
                            <a href="#" data-toggle="modal" data-target="#hold-orders-modal"
                               class="col-4 p-0 text-center border-right hold-items"
                               :class="{'disabled':orderHoldItems.length == 0 ||cart.length != 0 || salesOrReturnType == 'returns' }"
                               @click.prevent="">
                                <i class="la la-recycle la-2x p-2 app-color-text"></i><span
                                    class="badge badge-danger hold-items-badge-position" v-if="orderHoldItems.length>0">{{orderHoldItems.length}}</span>
                            </a>
                            <a href="#" data-toggle="modal" class="col-4 p-0 text-center border-right hold-cart"
                               :class="{'disabled':enableDisablePay()}" @click.prevent="orderHold()"
                               v-shortkey="holdCarditem"
                               @shortkey="commonMethodForAccessingShortcut('holdCard')">
                                <i class="la la-pause la-2x p-2 text-warning"></i>
                            </a>

                            <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                               class="col-4 p-0 text-center clear-cart" :class="{'disabled':cart.length==0}"
                               @click.prevent="" v-shortkey="cancelCarditem"
                               @shortkey="commonMethodForAccessingShortcut('cancelCarditem')">
                                <i class="la la-trash la-2x p-2 text-danger"></i>
                            </a>
                        </div>
                        <div v-else class="row mx-0 receiveDeleteButton">
                            <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                               class="col-12 p-0 text-center clear-cart" :class="{'disabled':cart.length==0}"
                               @click.prevent="">
                                <i class="la la-trash la-2x p-2 text-danger"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Product Variant Modal Structure -->
        <div class="modal fade" id="show-product-variant-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered big-modal-dialog" role="document">
                <div class="modal-content pt-3 px-3" v-if="selectedProductWithVariants">
                    <div class="mb-3">
                        <a href="#"
                           class="variant-modal-close-btn position-absolute p-2 close"
                           data-dismiss="modal"
                           aria-label="Close"
                           @click.prevent="">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <h5 class="m-0 text-center">{{ selectedProductWithVariants.title }}</h5>
                    </div>
                    <div class="row  mx-0">
                        <div class="col-4 mb-4" v-for="(variant,index) in selectedProductWithVariants.variants">
                            <!--{{product.title}}-->
                            <a href="#" class="app-color-text"
                               @click.prevent="addProductToCart(selectedProductWithVariants,variant.id)">
                                <div class="product-card bg-white border rounded">
                                    <div v-if="variant.imageURL" class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + variant.imageURL+ ')' }">
                                    </div>
                                    <div v-else class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + selectedProductWithVariants.productImage + ')'}">

                                    </div>
                                    <div class="product-variant-card-content  position-relative p-2">
                                        <div class="mb-2 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                            {{ variant.variant_title }}
                                        </div>
                                        <h6 class="product-card-font"
                                            v-for="(variantAttribute,index) in variant.attribute_values">
                                            {{ selectedProductWithVariants.attributeName[index] }} : {{ variantAttribute
                                            }}
                                        </h6>
                                        <h6 class="product-card-font position-absolute rounded-right app-color text-white"
                                            style="top: -3rem; padding: 2px 4px; left: 0;">
                                            {{ numberFormat(variant.price) }}
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Add Customer Modal Structure -->
        <div class="modal fade" id="customer-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <customer-create-edit v-if="isCustomerModalActive" class="modal-content" :order_type="order_type"
                                      :modalID="'#customer-add-edit-modal'"
                                      @newCustomer="newCustomer"></customer-create-edit>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Add suppiler Modal Structure -->
        <div class="modal fade" id="supplier-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <!--<customer-create-edit v-if="isCustomerModalActive" class="modal-content" :modalID="'#customer-add-edit-modal'"></customer-create-edit>-->
                <supplier-create-edit class="modal-content" v-if="isCustomerModalActive" :id="selectedItemId"
                                      :order_type="order_type"
                                      :modalID="'#supplier-add-edit-modal'"
                                      @newSupplier="newCustomer"></supplier-create-edit>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Show Cart Payment Modal Structure -->
        <div class="modal fade modal-hide" id="cart-payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered biggest-modal-dialog" role="document">
                <cart-payment-details v-if="isPaymentModalActive"
                                      :selectedCashRegisterID="selectedCashRegisterID"
                                      class="modal-content"
                                      :orderType="order_type"
                                      :salesOrReceivingType="salesOrReceivingType"
                                      :sold_to="sold_to"
                                      :sold_by="sold_by"
                                      :finalCart='finalCart'
                                      :user="user"
                                      :orderID="orderID"
                                      :logo="invoice_logo"
                                      :bankOrCardAmount="bankOrCardAmount"
                                      :bankOrCardOptions="bankOrCardOptions"
                                      :donePaymentShortcut="donePaymentItem"
                                      :calculateBank="calculateBank"
                                      :appName="appName"
                                      :transferBranch="selectedSearchBranch.id"
                                      :transferBranchName="selectedSearchBranch.name"
                                      @setDestroyCart="destroyCart"
                                      @amount="bankOrCardTransfer"
                >
                </cart-payment-details>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Show Card Payment Details Modal -->
        <div class="modal fade modal-hide" id="card-payment-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <card-payment-details v-if="isActiveTrans" :paid="paidAmount"
                                      @cardPayment="defaultPayment"></card-payment-details>
            </div>
        </div>
        <!-- End Card Payment Details Modal -->

        <!-- Show Bank Transfer Details Modal -->
        <div class="modal fade modal-hide" id="bank-transfer-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <bank-transfer-details v-if="isActiveTrans" :paid="paidAmount"
                                       @bankPayment="defaultPayment"></bank-transfer-details>
            </div>
        </div>
        <!-- End Bank Transfer Details Modal -->

        <!-- Holded Orders Modal Structure -->
        <div class="modal fade" id="hold-orders-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <pre-loader class="small-loader-container" v-if="!hideOrderHoldItemsPreLoader"></pre-loader>
                    <div class="p-4 text-center" v-else>
                        <a href="#" class="position-absolute variant-modal-close-btn p-2 close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <h4 class="mb-3">
                            {{trans('lang.hold_order_list')}}
                        </h4>
                        <div class="border rounded">
                            <div class="row mx-0 " :class="{'border-bottom': index!=orderHoldItems.length-1}"
                                 v-for="(orderHoldItem, index) in orderHoldItems">
                                <a href="#"
                                   class="col-10 d-flex align-items-center justify-content-center hold-items app-color-text"
                                   @click.prevent="setHoldOrderToCart(orderHoldItem)">

                                    <span v-if=""></span>{{ dateFormats(orderHoldItem.date) }} {{
                                    dateFormatsWithTime(orderHoldItem.date) }}
                                </a>
                                <a href="#" class="col-2">
                                    <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                                       class="col-4 p-0 text-center" @click.prevent="orderID=orderHoldItem.orderID">
                                        <i class="la la-trash la-2x p-2 text-danger hold-delete-icon"></i>
                                    </a>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Confirmation Modal -->
        <confirmation-modal id="clear-cart-modal"
                            :message="'order_will_be_deleted'"
                            :firstButtonName="'yes'"
                            :secondButtonName="'no'"
                            @confirmationModalButtonAction="confirmationModalButtonAction">
        </confirmation-modal>
        <!-- End Modal -->

        <!--Branch or cash register select modal-->
        <div class="modal fade" id="branch-or-cash-register-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <div class="modal-content modal-layout-content" v-if="isBranchModalActive">
                    <pre-loader v-if="!hideBranchPreLoader" class="small-loader-container"></pre-loader>
                    <div v-else>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="dashboard()">
                            <i class="la la-angle-left"></i> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">{{trans('lang.choose_branch')}}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':selectedBranchID==branch.id}" v-for="branch in branchList"
                               @click.prevent="selectBranch(branch.id,branch.name)">
                                {{ branch.name }}
                            </a>

                        </div>
                    </div>
                </div>
                <div class="modal-content modal-layout-content" v-if="isCashRegisterModalActive">
                    <pre-loader v-if="!hideCashRegisterPreLoader" class="small-loader-container"></pre-loader>
                    <div v-else>
                        <a href="#" class="variant-modal-close-btn position-absolute p-2 close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="" v-if="checkCashRegisterOpen()">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="branchModalAction(1), isCashRegisterModalActive = false">
                            <i class="la la-angle-left"></i> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">
                            {{trans('lang.select_cash_register')}}
                        </h6>
                        <div class="accordion" id="accordionExample">
                            <div class="card" v-for="(cashRegister,index) in cashRegisterList">
                                <div class="d-flex justify-content-between">
                                    <a href="#" :id="'cash-register-'+index" data-toggle="collapse"
                                       :data-target="'#collapse-'+index" aria-expanded="true"
                                       :aria-controls="'collapse-'+index"
                                       class="card-header app-color-text p-2 d-flex justify-content-between align-items-center border-bottom-0"
                                       :class="{'card-header-with-enroll-btn':!checkCashRegisterOpenByUser(cashRegister)} && cashRegister.multiple_access==1"
                                       @click.prevent="cashRegisterCollapse(index,cashRegister.id,cashRegister,cashRegister.status)">
                                        <div class="d-flex  align-items-center">
                                            <i class="la la-chevron-circle-right la-2x cart-icon"
                                               :class="{'cart-icon-rotate':cashRegister.showItemCollapse}"></i>
                                            <div>
                                                <div class="pl-2">{{ cashRegister.title }}</div>
                                                <div v-if="cashRegister.status=='open'"
                                                     class="pl-2 sales-cash-register">{{ cashRegister.register_status }}
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="cashRegister.status=='closed'"
                                              class="badge badge-danger badge-pill">{{trans('lang.closed')}}</span>
                                        <span v-else
                                              class="badge badge-success badge-pill">{{trans('lang.open')}}</span>
                                    </a>
                                    <a href="#"
                                       v-if="!checkCashRegisterOpenByUser(cashRegister) && cashRegister.multiple_access==1 && selectedCashRegisterID != cashRegister.id"
                                       class="p-2 text-white enroll-btn d-flex align-items-center font-weight-bold product-card-font"
                                       @click.prevent="setCashRegisterData(cashRegister,'enroll')">
                                        {{trans('lang.join')}}</a>
                                </div>
                                <div :id="'collapse-'+index" class="collapse border-top"
                                     :aria-labelledby="'cash-register-'+index" data-parent="#accordionExample">
                                    <div class="card-body card-body pb-3 pt-2 px-0">
                                        <form>
                                            <div class="row mx-0">
                                                <div class="mb-3 col-12" v-if="cashRegister.status=='open'">
                                                    <label :for="'note-'+index" class="label-in-cart">{{trans('lang.note')
                                                        }}{{cashRegister.note }}</label>
                                                    <!--<textarea :id="'note-'+index" name="note"-->
                                                    <!--class="form-control"-->
                                                    <!--v-model="cashRegister.note"></textarea>-->
                                                    <textarea :id="'note-'+index" :name="'note'"
                                                              v-validate="((openingAmount == closingAmount)  || note || noteValidation )? '':'required'"
                                                              class="form-control"
                                                              v-model="note"></textarea>
                                                    <div class="heightError">
                                                        <small class="text-danger" v-show="errors.has('note')">
                                                            {{errors.first('note') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-9" v-if="cashRegister.status=='closed'">
                                                    <label :for="'opening-amount-'+index">{{trans('lang.opening_amount')}}</label>

                                                    <payment-input :id="'opening-amount-'+index"
                                                                   v-model="cashRegister.openingAmount"></payment-input>

                                                </div>
                                                <div class="col-9" v-else>
                                                    <label>{{trans('lang.expected_closing_amount')}}:
                                                        {{numberFormat(expectedClosingAmount)}}</label>
                                                    <br>
                                                    <label :for="'closing-amount-'+index">{{trans('lang.closing_amount')}}</label>
                                                    <payment-input :id="'closing-amount-'+index"
                                                                   v-model="closingAmount"></payment-input>
                                                </div>
                                                <div class="col-3 mt-auto" v-if="cashRegister.status=='closed'">
                                                    <button class="btn app-color float-right"
                                                            :disabled="(cashRegister.openingAmount || cashRegister.openingAmount =='0')?false:true"
                                                            @click.prevent="setCashRegisterData(cashRegister,'open')">
                                                        {{trans('lang.open')}}
                                                    </button>
                                                </div>
                                                <div class="col-3 mt-auto" v-else>
                                                    <button class="btn btn-danger float-right"
                                                            :disabled="(disableCloseButton  && cashRegister.permision == 1)?false:true"
                                                            @click.prevent="setCashRegisterData(cashRegister,'close')">
                                                        {{trans('lang.close')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End modal-->

        <!-- Sales or returns  Type Select Modal Structure -->
        <div class="modal fade" id="sales-or-return-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <pre-loader v-if="!hideSalesReturnsPreLoader" class="small-loader-container"></pre-loader>
                    <div v-else>
                        <a href="#" class="variant-modal-close-btn position-absolute p-2 close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <h6 class="mb-3 text-center">{{trans('lang.select_sales_or_returns_type')}}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType=='sales'}"
                               @click.prevent="selectSalesOrReturnType('sales')">{{trans('lang.sales')}}</a>
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType=='returns'}"
                               @click.prevent="selectSalesOrReturnType('returns')">{{trans('lang.returns')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!--Sales orReceiving Type Select Modal Structure-->
        <div class="modal fade" id="sales-or-receiving-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <a href="#" class="variant-modal-close-btn position-absolute p-2 close" data-dismiss="modal"
                       aria-label="Close" @click.prevent="">
                        <i class="la la-close text-grey"></i>
                    </a>
                    <h6 class="mb-3 text-center">{{trans('lang.select_sales_or_receiving_type')}}</h6>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" v-if="order_type=='sales'"
                           :class="{'active':salesOrReceivingType=='customer'}"
                           @click.prevent="selectSalesOrReceivingType('customer')">{{trans('lang.customer')}}</a>
                        <a href="#" class="list-group-item list-group-item-action" v-else
                           :class="{'active':salesOrReceivingType=='supplier'}"
                           @click.prevent="selectSalesOrReceivingType('supplier')">{{trans('lang.supplier')}}</a>
                        <a href="#" class="list-group-item list-group-item-action"
                           :class="{'active':salesOrReceivingType == 'internal' && isActive}"
                           @click.prevent="selectSalesOrReceivingType('internal')">{{trans('lang.internal')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

    </div>
</template>
<script>
    import {delayCall} from "../../helper/delayCall";
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        props: ['user', 'order_type', 'sold_to', 'sold_by', 'addcustomer', 'manage_price', 'current_branch', 'current_cash_register', 'total_branch', 'sales_return_status', 'sales_receiving_type'],
        extends: axiosGetPost,
        data: () => ({
            //shortcuts
            productSearch: [],
            addCustomerShortKey: [],
            payment: [],
            holdCarditem: [],
            cancelCarditem: [],
            donePaymentItem: [],
            internalSalesBranch: '',
            internalSalesBranchId: '',
            discountOnEntire: 'discountOnEntire',
            discountOnAllItem: 'discountOnAllItem',
            prodictTotalWithoutDiscount: 0,
            appName: '',
            currentBranch: null,
            currentCashRegister: null,
            //products variables
            products: [],
            selectedProductWithVariants: null,
            productSearchValue: null,
            barcodeSearch: false,
            hideProductSearchPreLoader: false,
            //customers variables
            customers: [],
            customerNotAdded: true,
            selectedCustomer: [],
            customerSearchValue: '',
            isCustomerModalActive: false,
            isNewCustomerAdded: false,
            hideCustomerSearchPreLoader: false,
            //cart variables
            cart: [],
            newCart: [],
            isPaymentModalActive: false,
            //final cart variables
            finalCart: [],
            total: 0,
            subTotal: 0,
            grandTotal: 0,
            discount: null,
            profit: 0,
            overAllDiscount: null,
            tax: 0,
            orderID: null,
            salesOrReceivingType: null,
            salesOrReturnType: null,
            //order hold variables
            orderHoldItems: [],
            hideOrderHoldItemsPreLoader: '',
            //branch variables
            branchList: [],
            selectedBranchID: '',
            selectedBranchName: '',
            tempBranchID: null,
            hideBranchPreLoader: '',
            hideBranchSearchPreLoader: false,
            branchSearchValue: '',
            branches: [],
            selectedSearchBranch: [],
            //cash register variables
            cashRegisterList: [],
            selectedCashRegisterID: '',
            selectedCashRegisterName: '',
            selectedCashRegisterBranchID: '',
            hideCashRegisterPreLoader: '',
            status: '',
            isBranchModalActive: false,
            isCashRegisterModalActive: false,
            invoice_logo: '',
            activeProductId: '',
            activeVariantId: '',
            showDiscount: false,
            showOverAllDisc: false,
            hideCloseBtn: true,
            noteValidation: false,
            disableCloseButton: false,
            closingAmount: '',
            openingAmount: '',
            note: '',
            newCustomerId: '',
            paidAmount: '',
            bankOrCardAmount: '',
            bankOrCardOptions: {},
            orderSearchValue: '',
            orders: [],
            expectedClosingAmount: '',
            hideOrderSearchPreLoader: false,
            hideSalesReturnsPreLoader: true,
            isActiveTrans: false,
            calculateBank: false,
            newOverAllDiscount: null,
            newdiscount: null,
            isActiveTrans: false,
            calculateBank: false,
            isActive: false,
            // Active keyboard event
            open: null,
            highlightIndex: 0,
            intermediateSalesType: '',
            toggleCart: true,
            isSelectedBranch: true,
        }),
        watch: {
            discount: function (newVal, oldVal) {
                if (newVal != null && newVal != '') {
                    this.showDiscount = true;
                } else {
                    this.showDiscount = false;
                }
            },
            closingAmount: function (value) {
                if (value || value == '0') {
                    this.disableCloseButton = true;
                }
            },
            overAllDiscount: function (value) {
                if (value != null && value !== '') {
                    this.showOverAllDisc = true;
                    this.showDiscount = true;
                } else {
                    this.showOverAllDisc = false;
                    this.showDiscount = false;
                }
            }
        },
        created() {
            this.salesOrReceivingType = this.sales_receiving_type;
            this.isActive = "true";
            this.getShortCutValues();
            if (this.current_branch) {
                this.currentBranch = JSON.parse(this.current_branch);
                this.getProductData();
            }

            if (!this.currentBranch) {
                this.getBranchData();
            } else {
                if (JSON.parse(this.current_branch).is_cash_register == 1) {
                    if (this.current_cash_register) {
                        this.currentCashRegister = JSON.parse(this.current_cash_register);
                    }
                    if (!this.currentCashRegister) {
                        this.getCashRegisterData();
                    } else {
                        this.getExpectedAmount(this.currentCashRegister.id);
                        this.selectedCashRegisterID = this.currentCashRegister.id;
                        this.selectedCashRegisterName = this.currentCashRegister.title;
                        this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;
                    }
                }
                this.selectedBranchID = this.currentBranch.id;
                this.selectedBranchName = this.currentBranch.name;
                if (JSON.parse(this.current_branch).is_cash_register == 1) {
                    if (this.current_cash_register) {
                        this.currentCashRegister = JSON.parse(this.current_cash_register);
                    }
                    if (!this.currentCashRegister) {
                        this.getCashRegisterData();
                    } else {
                        this.getExpectedAmount(this.currentCashRegister.id);
                        this.selectedCashRegisterID = this.currentCashRegister.id;
                        this.selectedCashRegisterName = this.currentCashRegister.title;
                        this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;
                    }
                }
                this.getProductData();
            }
            this.getHoldOrders();
            this.getInvoiceData('/invoice-logo');
        },
        mounted() {
            let instance = this;

            $(document).ready(function () {
                $("#pop_mouse1").click(function () {
                    $("input").focus();
                });
                $("#pop_mouse2").click(function () {
                    $("input").focus();
                });
            });

            if (this.order_type == 'sales') {
                this.salesOrReturnType = this.sales_return_status;
            }

            let stopPropagationElements = document.querySelectorAll("#d-1, #d-2");
            for (let stopPropagationElement of stopPropagationElements) {
                stopPropagationElement.addEventListener("click", function () {
                    event.stopPropagation();
                });
            }


            this.setCartItemsToCookieOrDB();

            this.$hub.$on('customerAddedFromSales', function () {
                instance.isNewCustomerAdded = true;
                instance.getCustomerData();
            });

            if (!this.currentBranch) {
                this.isBranchModalActive = true;
                this.openbranchORCashRegisterSelectModal();
            } else {
                if (JSON.parse(this.current_branch).is_cash_register == 1) {
                    if (!this.currentCashRegister) {
                        this.isCashRegisterModalActive = true;
                        this.openbranchORCashRegisterSelectModal();
                    } else {
                        if (this.currentCashRegister.branchID != this.currentBranch.id) {
                            this.isCashRegisterModalActive = true;
                            this.getCashRegisterData();
                            this.openbranchORCashRegisterSelectModal();
                        }
                    }
                }
            }

            $('#branch-or-cash-register-select-modal').on('hidden.bs.modal', function (e) {
                instance.isBranchModalActive = false;
                instance.isCashRegisterModalActive = false;
            });

            $('#show-product-variant-modal').on('hidden.bs.modal', function (e) {
                instance.selectedProductWithVariants = null;
            });

            $('#cart-payment-modal').on('hidden.bs.modal', function (e) {
                instance.isPaymentModalActive = false;
            });

            $('#customer-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isCustomerModalActive = false;
            });
            $('#supplier-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isCustomerModalActive = false;
            });

            this.$hub.$on('setOrderID', function (orderID) {
                instance.getExpectedAmount(instance.selectedCashRegisterID);
                instance.orderID = orderID;
                instance.setCartItemsToCookieOrDB(1);
            })

            this.$nextTick(function () {
                instance.cartMinHeightSet();
            });

            $(window).resize(function () {
                instance.cartMinHeightSet();
                instance.productHeightSet();
                instance.productVariantHeightSet();
            });

            //JQuery code for hide parent cart details modal while open card payment modal
            var modal_lv = 0;
            $('.modal-hide').on('shown.bs.modal', function (e) {
                $('.modal-backdrop:last').css('zIndex', 1051 + modal_lv);
                $(e.currentTarget).css('zIndex', 1052 + modal_lv);
                modal_lv++
            });
            $('.modal-hide').on('hidden.bs.modal', function (e) {
                modal_lv--
            });
            $('#bank-transfer-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveTrans = false;
            });
            $('#card-payment-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveTrans = false;
            });

        },
        methods: {
            enableDisablePay(){

                let cartData = this.cart.filter(function(element){
                    return element.orderType != 'discount';
                });

                if(cartData.length==0){
                    return true;
                }else{
                    return false;
                }
            },
            setQuantityInCart(value, index) {
                this.cart[index].quantity = value;
                this.setCartItemsToCookieOrDB(1);
            },
            suggestionSelected(suggestion) {
                this.open = false
                this.customerSearchValue = suggestion[0]
                this.branchSearchValue = suggestion[0]
                this.$emit('input', suggestion[1])
            },

            up() {
                if (this.open) {
                    if (this.highlightIndex > 0) {
                        this.highlightIndex--
                    }
                } else {
                    this.setOpen(true)
                }
            },
            down() {
                if (this.open) {
                    if (this.highlightIndex < this.customers.length - 1 || this.highlightIndex < this.branches.length - 1) {
                        this.highlightIndex++
                    }
                } else {
                    this.setOpen(true)
                }
            },
            getExpectedAmount(id) {
                let instance = this;
                if (id != null && id != '') {
                    this.axiosGet('/get-register-amount/' + id,
                        function (response) {
                            instance.expectedClosingAmount = response.data;
                        },
                        function (error) {

                        }
                    );
                }
            },
            commonMethodForAccessingShortcut(data) {
                if (data == "productSearch" && this.shortcutKeyInfo.productSearchShortcut.status == 1 && this.shortcutStatus == 1) {
                    this.$refs.search.focus();
                }
                if (data == "addCustomer" && this.shortcutKeyInfo.addCustomerShortcut.status == 1 && this.shortcutStatus == 1) {
                    this.addCustomer();
                }
                if (data == "pay" && this.shortcutKeyInfo.payShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                    this.pay();
                }
                if (data == "holdCard" && this.shortcutKeyInfo.holdCardShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                    this.holdCard();
                }
                if (data == "cancelCarditem" && this.shortcutKeyInfo.cancelCardShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                    this.cancelCard();
                }
            },
            bankOrCardTransfer(amount, modalId) {
                this.isActiveTrans = true;
                this.calculateBank = false,
                    $(modalId).modal('show');
                this.paidAmount = amount;
            },
            defaultPayment(amount, options) {
                this.calculateBank = true,
                    this.bankOrCardAmount = amount;
                this.bankOrCardOptions = options;
            },
            getShortCutValues() {
                let instance = this;
                instance.axiosGet('/shortcut-setting-data/{id}',
                    function (response) {
                        let shortcutCollection = response.data.shortcutSettings;

                        //product search
                        if (shortcutCollection.productSearch.shortcut_key.includes("+")) instance.productSearch = shortcutCollection.productSearch.shortcut_key.split("+");
                        else instance.productSearch = [shortcutCollection.productSearch.shortcut_key];

                        //addcustomer
                        if (shortcutCollection.addCustomer.shortcut_key.includes("+")) instance.addCustomerShortKey = shortcutCollection.addCustomer.shortcut_key.split("+");
                        else instance.addCustomerShortKey = [shortcutCollection.addCustomer.shortcut_key];

                        //holdcart
                        if (shortcutCollection.holdCard.shortcut_key.includes("+")) instance.holdCarditem = shortcutCollection.holdCard.shortcut_key.split("+");
                        else instance.holdCarditem = [shortcutCollection.holdCard.shortcut_key];

                        //pay
                        if (shortcutCollection.pay.shortcut_key.includes("+")) instance.payment = shortcutCollection.pay.shortcut_key.split("+");
                        else instance.payment = [shortcutCollection.pay.shortcut_key];
                        //cancelCard
                        if (shortcutCollection.cancelCarditem.shortcut_key.includes("+")) instance.cancelCarditem = shortcutCollection.cancelCarditem.shortcut_key.split("+");
                        else instance.cancelCarditem = [shortcutCollection.cancelCarditem.shortcut_key];
                        //donePayment
                        if (shortcutCollection.donePayment1.shortcut_key.includes("+")) instance.donePaymentItem = shortcutCollection.donePayment1.shortcut_key.split("+");
                        else instance.donePaymentItem = [shortcutCollection.donePayment1.shortcut_key];
                    },
                    function (error) {

                    },
                );
            },
            addCustomer(event) {
                $('#customer-add-edit-modal').modal('show')
                this.isCustomerModalActive = true;
            },
            holdCard(event) {
                this.orderHold();
            },
            cancelCard(event) {
                $('#clear-cart-modal').modal('show')
            },
            pay(event) {
                this.cartSave();
                $('#cart-payment-modal').modal('show')
                this.isPaymentModalActive = true;
            },
            allProductDiscount(value, index, unformatted) {
                let instance = this;
                instance.discount = value;
                instance.newdiscount = unformatted;
                this.cart.forEach(function (element) {
                    if (element.quantity > 0) {
                        element.discount = instance.discount;
                    }
                });
                instance.subTotalAmount();
                instance.setCartItemsToCookieOrDB(1);
            },
            addOverAllDiscount(value, index, unformatted) {
                this.overAllDiscount = value;
                this.newOverAllDiscount = unformatted;
                let flag = true;
                let instance = this;
                if (this.overAllDiscount) {
                    instance.cart.forEach(function (element) {
                        if (element.orderType === 'discount') {
                            element.price = instance.overAllDiscount;
                            element.calculatedPrice = -(instance.overAllDiscount);
                            flag = false;
                        }
                    });
                    if (flag) {
                        instance.cart.push({
                            productID: null,
                            variantID: null,
                            taxID: null,
                            orderType: 'discount',
                            productTitle: instance.trans('lang.discount'),
                            price: this.overAllDiscount,
                            quantity: -1,
                            discount: null,
                            calculatedPrice: -(instance.overAllDiscount),
                            cartItemNote: '',
                            showItemCollapse: false,
                        });
                    }
                } else {
                    instance.cart = instance.cart.filter(element => element.orderType !== 'discount');
                }
                instance.setCartItemsToCookieOrDB(1);
            },
            getInvoiceData(route) {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.invoice_logo = response.data.logo.setting_value;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            capitalizeFirstLetter(value) {
                return _.startCase(_.toLower(value));
            },
            selectSalesOrReceivingType(value) {
                let instance = this;
                this.getHoldOrders();
                this.isActive = "true";
                instance.salesOrReceivingType = value;
                this.adjustPrice();
                $('#sales-or-receiving-type-select-modal').modal('hide')

                this.axiosPost('/sales-receiving-type-set', {
                    salesOrReceivingType: value,
                    orderType: instance.order_type,
                }, function (response) {

                }, function (error) {

                });
            },
            adjustPrice() {
                let instance = this;
                if (instance.order_type == 'sales') {
                    instance.products.forEach(function (product) {
                        product.variants.forEach(function (variant) {
                            if (instance.salesOrReceivingType == 'customer') variant.price = variant.selling_price;
                            else variant.price = variant.purchase_price;
                        });
                    })
                }
            },
            selectSalesOrReturnType(value) {
                let instance = this;
                instance.hideSalesReturnsPreLoader = false;
                instance.axiosGETorPOST(
                    {
                        url: '/sales-returns-type-set',
                        postData: {salesOrReturnType: value},
                    },
                    (success, responseData) => {

                        if (success) //response after then function
                        {
                            instance.salesOrReturnType = value;
                            instance.hideSalesReturnsPreLoader = true;
                            $('#sales-or-return-type-select-modal').modal('hide')
                        } else {
                            instance.hideSalesReturnsPreLoader = true;
                            $('#sales-or-return-type-select-modal').modal('hide')
                        }
                    }
                );
            },
            openbranchORCashRegisterSelectModal() {
                $('#branch-or-cash-register-select-modal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            },
            confirmationModalButtonAction() {
                if (this.orderID) {
                    this.cancelOrder();
                }

                this.destroyCart(true);
                this.overAllDiscount = null;
                this.newOverAllDiscount = null;
                this.selectedSearchBranch = [];
                this.isSelectedBranch = true;
                $('#clear-cart-modal').modal('hide');
            },
            cancelOrder() {
                this.hideOrderHoldItemsPreLoader = false;
                let instance = this;
                this.axiosGETorPOST(
                    {
                        url: '/sales-cancel', //set url
                        postData: {orderID: this.orderID} //set post data
                    },
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            this.getHoldOrders(true);
                        }
                        // else { //response after catch function
                        //     // do something
                        // }
                    }
                );

            },
            cartMinHeightSet() {
                let section1 = $("#cart-section-1").height();
                let section2 = $("#cart-section-3").position();
                $("#cart-section-2").css('max-height', (section2.top - section1) + 'px');
            },
            searchProductInput(event) {
                let instance = this;

                if (instance.productSearchValue) {

                    if (event.keyCode == 13) {
                        instance.barcodeSearch = true;
                        instance.getProductData();
                        instance.productSearchValue = null;
                    } else {
                        delayCall(function () {
                            if (instance.productSearchValue) {
                                instance.hideProductSearchPreLoader = false;
                                instance.getProductData();
                            }
                        });
                    }
                } else {
                    instance.getProductData();
                }
            },
            searchCustomerInput(event) {
                let instance = this;
                instance.hideCustomerSearchPreLoader = false;
                if (!this.open) {
                    this.open = true
                }
                this.highlightIndex = 0;
                if (instance.customerSearchValue) {

                    delayCall(function () {
                        instance.customers = [];
                        instance.getCustomerData();
                    });
                } else {
                    instance.hideCustomerSearchPreLoader = true;

                }

            },
            searchBranchInput(event) {
                let instance = this;
                instance.hideCustomerSearchPreLoader = false;
                if (!this.open) {
                    this.open = true
                }
                this.highlightIndex = 0;
                if (instance.branchSearchValue) {

                    delayCall(function () {
                        instance.branches = [];
                        instance.getInternalBranchData();
                    });
                } else {
                    instance.hideCustomerSearchPreLoader = true;

                }

            },
            searchOrderInput(event) {
                let instance = this;
                instance.hideOrderSearchPreLoader = false;
                if (instance.orderSearchValue) {

                    delayCall(function () {
                        instance.orders = [];
                        instance.getOrderData();
                    });
                } else {
                    instance.hideOrderSearchPreLoader = true;
                }
            },
            getOrderData() {

                let instance = this;
                instance.axiosGETorPOST(
                    {
                        url: '/get-return-orders', //set url
                        postData: {
                            orderId: instance.orderSearchValue,
                        } //set post data
                    },
                    (success, responseData) => { // callback after axios method call

                        if (success) //response after then function
                        {
                            instance.orders = responseData;

                            instance.hideOrderSearchPreLoader = true;
                        }
                    });
            },
            variantProductCard(productVariantInfo) {
                if (productVariantInfo.length > 1) {
                    return '#show-product-variant-modal';
                }
            },
            productCardAction(product) {
                if (product.variants.length == 1) {
                    this.addProductToCart(product, product.variants[0].id);
                } else {
                    this.selectedProductWithVariants = product;
                    let instance = this;
                    setTimeout(function () {
                        instance.productVariantHeightSet();
                    }, 200)
                }
            },
            destroyCart(check) {
                if (check) {
                    this.deleteCartItemsFromCookieOrDB();
                    this.cart = [];
                    this.discount = null;
                    this.tax = 0;
                    this.grandTotal = 0;
                    this.total = 0;
                    this.subTotal = 0;
                    this.date = null;
                    this.selectedCustomer = [];
                    this.customerNotAdded = true;
                    this.customerSearchValue = '';
                    this.branchSearchValue = '';
                    this.overAllDiscount = null;
                    this.newOverAllDiscount = null;
                    this.newdiscount = null;
                    this.selectedSearchBranch = [];
                    this.isSelectedBranch = true;
                }
            },
            addProductToCart(product, productVariantID) {
                let flag = 0,
                    instance = this;
                instance.activeProductId = product.productID;
                instance.activeVariantId = productVariantID;

                setTimeout(function () {
                    instance.activeProductId = '';
                    instance.activeVariantId = '';
                }, 1000);
                if (this.cart.length > 0) {
                    this.cart.forEach(function (cartItem, index, cartArray) {
                        if (cartItem.productID == product.productID && cartItem.variantID == productVariantID) {
                            cartArray[index].quantity++;
                            flag = 1;
                        }
                    });
                }
                if (flag == 0) {
                    let insertCheckedVariant = _.filter(product.variants, ['id', productVariantID]);
                    if (!_.isEmpty(insertCheckedVariant)) {
                        this.cart.push({
                            productID: product.productID,
                            productTitle: product.title,
                            taxID: product.tax_id,
                            orderType: instance.order_type,
                            productTaxPercentage: product.taxPercentage,
                            variantID: insertCheckedVariant[0].id,
                            price: insertCheckedVariant[0].price,
                            unformPrice: insertCheckedVariant[0].price,
                            purchase_price: insertCheckedVariant[0].purchase_price,
                            variantTitle: insertCheckedVariant[0].variant_title,
                            quantity: 1,
                            discount: this.discount,
                            calculatedPrice: insertCheckedVariant[0].price,
                            cartItemNote: '',
                            showItemCollapse: false,
                        });
                    }
                }
                this.setCartItemsToCookieOrDB(1);
                this.newCart = this.cart;
                $('#show-product-variant-modal').modal('hide');
            },
            setPropductNewPrice(price, index, value) {
                this.cart[index].price = price;
                this.cart[index].unformPrice = value;
                this.setCartItemsToCookieOrDB(1);
            },
            setCartItemsToCookieOrDB(flag = 0) {
                let cookieName = "user-" + this.user.id + "-" + this.order_type + "-cart",
                    cookieObject = {
                        'cart': this.cart,
                        'customer': this.selectedCustomer,
                        'branch': this.selectedSearchBranch,
                        'orderID': this.orderID,
                        'discount': this.discount,
                        'overAllDiscount': this.overAllDiscount,
                    };

                if (!window.$cookies.isKey(cookieName)) {
                    window.$cookies.set(cookieName, cookieObject, "4m");
                } else {
                    if (flag == 0) {
                        let cookieValue = window.$cookies.get(cookieName);
                        let cookieCart = cookieValue.cart;

                        cookieCart.forEach(function (cookieCartItem, index, array) {
                            if (cookieCartItem.showItemCollapse) {
                                array[index].showItemCollapse = false;
                            }
                        }),
                            this.cart = cookieCart;
                        this.selectedCustomer = cookieValue.customer;
                        if (cookieValue.branch != undefined && this.salesOrReceivingType == 'internal') {
                            if (cookieValue.branch.length == 0) {
                                this.isSelectedBranch = true;
                            } else {
                                this.selectedSearchBranch = cookieValue.branch;
                                this.isSelectedBranch = false;

                            }
                        } else {
                            this.isSelectedBranch = true;
                        }
                        this.discount = cookieValue.discount;
                        this.newdiscount = cookieValue.discount;
                        this.overAllDiscount = cookieValue.overAllDiscount;
                        this.newOverAllDiscount = cookieValue.overAllDiscount;
                        this.orderID = cookieValue.orderID;
                        if (this.selectedCustomer.length == 1) {
                            this.customerNotAdded = false;
                        }

                    } else {
                        window.$cookies.set(cookieName, cookieObject, "4m");
                    }
                }

                this.subTotalAmount();

            },
            subTotalAmount() {
                let instance = this;
                this.total = 0;
                this.profit = 0;
                this.tax = 0;
                this.subTotal = 0;

                this.cart.forEach(function (cartItem) {

                    instance.prodictTotalWithoutDiscount += cartItem.price;
                    let calculatedPriceForSub = 0;

                    if (cartItem.quantity > 0) {
                        cartItem.calculatedPrice = cartItem.price * cartItem.quantity;

                        if (cartItem.orderType != "discount"){
                            calculatedPriceForSub = cartItem.calculatedPrice - (cartItem.calculatedPrice * cartItem.discount / 100);
                        }

                        if (cartItem.discount) {
                            cartItem.calculatedPrice = cartItem.calculatedPrice - (cartItem.calculatedPrice * cartItem.discount / 100);
                            instance.tax += ((cartItem.calculatedPrice) * cartItem.productTaxPercentage) / 100;
                        } else {
                            instance.tax += (cartItem.calculatedPrice * cartItem.productTaxPercentage) / 100;
                        }

                    }
                    instance.total += cartItem.calculatedPrice;
                    instance.subTotal += calculatedPriceForSub;

                    if (cartItem.orderType !== 'discount') {
                        instance.profit += cartItem.calculatedPrice - (cartItem.purchase_price * cartItem.quantity);
                    } else {
                        instance.profit -= cartItem.price;
                    }

                });
                this.grandTotal = Number((this.total + this.tax).toFixed(2));
            },
            deleteCartItemsFromCookieOrDB() {
                window.$cookies.remove("user-" + this.user.id + "-" + this.order_type + "-cart");
            },
            cartItemButtonAction(cartProductID, cartVariantID, orderType, action) {
                let instance = this;
                this.cart.forEach(function (cartItem, index, cartArray) {
                    if (cartItem.productID == cartProductID && cartItem.variantID == cartVariantID) {
                        if (action == '+') {
                            cartArray[index].quantity++;
                            if (cartItem.quantity == 0) {
                                cartArray.splice(index, 1);
                            }
                            if (instance.order_type === 'sales' && instance.salesOrReturnType === 'returns') {
                                cartItem.calculatedPrice = cartItem.quantity * cartItem.price;
                            }
                        } else if (action == '-') {
                            --cartArray[index].quantity;

                            if (cartItem.quantity == 0) {
                                cartArray.splice(index, 1);
                            }
                        } else {
                            if (orderType === 'discount') {
                                instance.overAllDiscount = null;
                                instance.newOverAllDiscount = null;
                            }
                            cartArray.splice(index, 1);
                        }
                    }

                    if (instance.cart.length == 0) {
                        instance.discount = null;
                        instance.newdiscount = null;
                    }
                });

                this.setCartItemsToCookieOrDB(1);

            },
            cartItemCollapse(index, variantID) {
                let instance = this;
                this.cart.forEach(function (cartItem, i, array) {
                    if (i === index && cartItem.variantID === variantID && cartItem.orderType !== 'discount' && !(instance.order_type === 'sales' && instance.salesOrReturnType === 'returns')) {
                        array[i].showItemCollapse = !array[i].showItemCollapse;
                    } else {
                        array[i].showItemCollapse = false;
                    }
                });
            },
            cashRegisterCollapse(index, cashRegisterID, cashRegister, status) {
                this.$validator.reset();
                this.note = '';
                if (cashRegister.opening_amount) {
                    this.openingAmount = cashRegister.opening_amount;
                }
                if (status == 'closed') {
                    this.noteValidation = true;
                } else {
                    this.noteValidation = false;
                }
                this.cashRegisterList.forEach(function (cashRegister, i, array) {
                    if (i == index && cashRegister.id == cashRegisterID) {
                        array[i].showItemCollapse = !array[i].showItemCollapse;
                    } else {
                        array[i].showItemCollapse = false;
                    }
                });
            },
            selectCustomer(customer) {
                this.selectedCustomer = [];
                this.selectedCustomer.push(customer);
                this.customerNotAdded = false;
                if(customer.customer_group_discount != undefined){
                this.allProductDiscount(customer.customer_group_discount)
                }
                this.setCartItemsToCookieOrDB(1);
            },
            selectSearchBranch(branch) {
                this.selectedSearchBranch = branch;
                this.branchSearchValue = '';
                this.isSelectedBranch = false;
                this.setCartItemsToCookieOrDB(1);
            },
            removeSelectedCustomer(index) {
                this.customerNotAdded = true;
                this.selectedCustomer.splice(index, 1);
                this.customerSearchValue = '';
                this.discount = null;
                this.allProductDiscount();
                this.setCartItemsToCookieOrDB(1);
            },
            removeSelectedBranch() {
                this.$emit('close');
                this.selectedSearchBranch = [];
                this.isSelectedBranch = true;
            },
            orderHold() {
                this.cartSave('hold');
            },
            cartSave(status = 'done') {
                if (status == 'done') {
                    this.isPaymentModalActive = true;
                }

                let selectCustomerForCart;

                if (this.selectedCustomer[0]) {
                    selectCustomerForCart = this.selectedCustomer[0];
                }

                this.finalCart = {
                    orderID: this.orderID,
                    orderType: this.order_type,
                    salesOrReceivingType: this.salesOrReceivingType,
                    createdBy: this.user.id,
                    status: status,
                    cart: this.cart,
                    customer: selectCustomerForCart,
                    subTotal: this.subTotal,
                    discount: this.discount,
                    tax: this.tax,
                    profit: this.profit,
                    grandTotal: this.grandTotal,
                    cartNote: '',
                    transferBranch: this.selectedSearchBranch.id,
                    date: moment().format('YYYY-MM-DD h:mm A'),
                }

                if (status == 'hold') {
                    let instance = this;
                    instance.axiosGETorPOST(
                        {
                            url: '/store', //set url
                            postData: this.finalCart //set post data
                        },
                        (success, responseData) => { // callback after axios method call
                            if (success) //response after then function
                            {
                                instance.orderID = null;
                                instance.selectedSearchBranch = [];
                                instance.isSelectedBranch = true;
                                instance.destroyCart(true);
                                instance.getHoldOrders(true);
                            }
                        }
                    );
                }
            },
            productHeightSet() {
                $("div.product-card-content").removeAttr("style");
                this.$nextTick(function () {
                    var maxHeight = 0;

                    $("div.product-card-content").each(function () {
                        if ($(this).height() > maxHeight) {
                            maxHeight = $(this).height();
                        }
                    });
                    $("div.product-card-content").css('height', maxHeight + 'px');
                })
            },
            productVariantHeightSet() {
                $("div.product-variant-card-content .d-flex").removeAttr("style");
                var maxHeight2 = 0;

                $("div.product-variant-card-content .d-flex").each(function (e) {

                    if ($(this).height() > maxHeight2) {
                        maxHeight2 = $(this).height();
                    }
                });

                $("div.product-variant-card-content .d-flex").css('height', maxHeight2 + 'px');
            },
            getProductData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {
                        url: '/sales-product', //set url
                        postData: {
                            searchValue: this.productSearchValue,
                            order_type: this.order_type
                        } //set post data
                    },
                    (success, responseData) => { // callback after axios method call

                        this.appName = responseData.appName.setting_value;

                        let shortcutsKey = responseData.shortcutKeyCollection.allKeyboardShortcut;

                        if (success) //response after then function
                        {
                            if (responseData.barcodeResultValue) {
                                instance.barcodeSearch = true;
                                instance.barcodeSearchedProductAddToCart(responseData.barcodeResultValue);
                            } else {
                                instance.products = responseData.products;
                            }

                            instance.$nextTick(function () {
                                instance.productHeightSet();
                            });
                        }
                        // else { //response after catch function
                        //     // do something
                        // }
                        instance.adjustPrice();
                        instance.hideProductSearchPreLoader = true;
                    });
            },
            barcodeSearchedProductAddToCart(data) {
                let flag = 0;
                if (this.barcodeSearch) {
                    if (this.cart.length > 0) {
                        this.cart.forEach(function (cartItem, index, cartArray) {
                            if (cartItem.productID == data.productID && cartItem.variantID == data.variantID) {
                                cartArray[index].quantity++;
                                flag = 1;
                            }
                        });
                    }

                    if (flag == 0) {
                        this.cart.push({
                            productID: data.productID,
                            productTitle: data.productTitle,
                            taxID: data.taxID,
                            orderType: this.order_type,
                            productTaxPercentage: data.productTaxPercentage,
                            variantID: data.variantID,
                            price: data.price,
                            unformPrice: data.price,
                            purchase_price: data.purchase_price,
                            variantTitle: data.variantTitle,
                            quantity: 1,
                            discount: this.discount,
                            calculatedPrice: data.price,
                            cartItemNote: '',
                            showItemCollapse: false,
                        });
                    }
                    this.setCartItemsToCookieOrDB(1);
                    this.productSearchValue = '';
                    this.getProductData();
                    this.barcodeSearch = false;
                }
            },
            getInternalBranchData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {
                        url: '/internal-sales-branch', //set url
                        postData: {
                            searchValue: this.branchSearchValue,
                            branchId: this.currentBranch.id
                        } //set post data
                    },
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            instance.branches = responseData;
                        }
                        instance.hideCustomerSearchPreLoader = true;
                    }
                );
            },
            getCustomerData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {
                        url: '/customers-list', //set url
                        postData: {
                            customerSearchValue: this.customerSearchValue,
                            orderType: this.order_type
                        } //set post data
                    },
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            instance.customers = responseData;

                            if (instance.isNewCustomerAdded) {
                                instance.selectCustomer(0);
                            }
                            instance.isNewCustomerAdded = false;
                        }
                        instance.hideCustomerSearchPreLoader = true;
                        if (instance.newCustomerId) {
                            let customer = instance.customers.filter(function (element) {
                                return element.id === instance.newCustomerId;
                            });

                            instance.selectCustomer(customer[0]);
                            instance.newCustomerId = '';
                        }
                    }
                );
            },
            getBranchData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {url: '/branches'}, //set url
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            instance.branchList = responseData;
                            if (instance.currentBranch) //check if branch is selected by user previously
                            {
                                instance.branchList.forEach(function (branch) {

                                    if (instance.currentBranch.id == branch.id) //checks if branch id matches
                                    {
                                        instance.selectedBranchID = branch.id; //set branch id
                                        instance.selectedBranchName = branch.name; //set branch name
                                    }
                                })
                            }
                        }

                        instance.hideBranchPreLoader = true;

                    });
            },
            selectBranch(branchID, branchName) {
                if (this.selectedBranchID == branchID) {
                    $('#branch-or-cash-register-select-modal').modal('hide');
                } else {
                    this.selectedBranchID = branchID;
                    this.selectedBranchName = branchName;
                    if (!this.currentBranch || this.currentBranch.id != this.selectedBranchID) {
                        this.setBranchData();
                        if (!this.currentBranch) {
                            this.currentBranch = [];
                        }
                    }
                }
            },
            setBranchData() {

                let instance = this;
                this.hideBranchPreLoader = false;
                this.tempBranchID = this.selectedBranchID;
                instance.axiosGETorPOST(
                    {
                        url: '/sales-branch-set',
                        postData: {branchID: this.selectedBranchID},
                    },
                    (success, responseData) => {

                        if (success) //response after then function
                        {
                            instance.hideBranchPreLoader = true;
                            instance.isBranchModalActive = false;
                            instance.isCashRegisterModalActive = true;
                            instance.hideCashRegisterPreLoader = false;
                            this.currentBranch.id = this.selectedBranchID;
                            this.currentBranch.name = this.selectedBranchName;
                            this.checkCashRegister(this.currentBranch.id);
                            this.getProductData();
                        }
                    }
                );
            },
            checkCashRegister(branch_id) {
                let instance = this;
                this.axiosGet('/edit-branch/' + branch_id,
                    function (response) {
                        if (response.data.is_cash_register == 1) {
                            instance.getCashRegisterData();
                        } else {
                            instance.isCashRegisterModalActive = false;
                            instance.hideCashRegisterPreLoader = true;
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                    },
                    function (response) {

                    },
                );
            },
            getHoldOrders(newOrderHold = false) {
                let instance = this;
                this.hideOrderHoldItemsPreLoader = false;
                instance.axiosGETorPOST(
                    {url: '/get-hold-orders'}, //set url
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            responseData.forEach(function (orderHoldItem, index, array) {
                                if (orderHoldItem.orderID == instance.orderID) {
                                    array.splice(index, 1); //removing data from orderHoldItems if orderID matches which is set previously
                                }
                            });

                            if (instance.order_type == 'sales') {
                                instance.orderHoldItems = responseData.filter(function (element) {

                                    return element.salesOrReceivingType == instance.salesOrReceivingType;
                                });
                            } else {
                                instance.orderHoldItems = responseData;
                            }


                            instance.hideOrderHoldItemsPreLoader = true;
                            if (instance.orderHoldItems.length == 0) {
                                $('#hold-orders-modal').modal('hide');
                            }
                        }
                    }
                );
            },
            setHoldOrderToCart(holdItem) {
                let instance = this;
                if (holdItem.all_discount !== 0) instance.discount = holdItem.all_discount;
                holdItem.cart.forEach(function (product) {
                    if (product.orderType === 'discount') {
                        product.productTitle = instance.trans('lang.discount');
                        instance.overAllDiscount = product.calculatedPrice;
                        instance.newOverAllDiscount = product.calculatedPrice;
                        product.quantity = -1;
                        product.calculatedPrice = -(product.calculatedPrice);
                    }
                });

                if (holdItem.transfer_branch_id == null) {
                    instance.selectedSearchBranch = [];
                    instance.isSelectedBranch = true;
                } else {
                    instance.selectedSearchBranch = {
                        name: holdItem.transfer_branch_name,
                        id: holdItem.transfer_branch_id
                    }
                    instance.isSelectedBranch = false;
                }

                if (instance.cart.length == 0) {
                    instance.cart = holdItem.cart;
                    instance.orderID = holdItem.orderID;

                    if (holdItem.customer) {
                        instance.selectedCustomer.push(holdItem.customer);
                        instance.customerNotAdded = false;
                    }

                    instance.cart.forEach(function (element) {
                        element.unformPrice = element.price;
                    })
                    instance.setCartItemsToCookieOrDB(1);

                    instance.orderHoldItems.forEach(function (orderHoldItem, index, array) {
                        if (orderHoldItem.orderID == holdItem.orderID) {
                            array.splice(index, 1);
                        }

                    });
                    $('#hold-orders-modal').modal('hide');
                } else {
                    $('#clear-cart-modal').modal();
                }
            },
            selectOrder(order) {

                let instance = this;
                instance.orderSearchValue = '';
                if (order.all_discount !== 0) instance.discount = order.all_discount;
                order.cart.forEach(function (product) {
                    if (product.orderType === 'discount') {
                        product.productTitle = instance.trans('lang.discount');
                        instance.overAllDiscount = -(product.calculatedPrice);
                        instance.newOverAllDiscount = -(product.calculatedPrice);
                        product.calculatedPrice = -(product.calculatedPrice);
                    }
                });
                if (instance.cart.length == 0) {
                    instance.cart = order.cart;
                    if (order.customer) {
                        instance.selectedCustomer.push(order.customer);
                        instance.customerNotAdded = false;
                    }
                    instance.setCartItemsToCookieOrDB(1);

                } else {

                }
            },
            branchModalAction(flag = 0) {
                this.isBranchModalActive = true;
                if (flag === 0) {
                    this.openbranchORCashRegisterSelectModal();
                }
                if (this.branchList.length == 0) {
                    this.getBranchData();
                }
            },
            cashRegisterModalAction() {
                this.isCashRegisterModalActive = true;
                this.openbranchORCashRegisterSelectModal();
                if (this.cashRegisterList.length == 0) {
                    this.getCashRegisterData();
                }
            },
            getCashRegisterData() {
                let instance = this,
                    tempData;
                instance.axiosGETorPOST(
                    {url: '/cash-registers'}, //set url
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            tempData = responseData;
                            _.mapValues(tempData, function (cashRegister) {
                                cashRegister.openingAmount = null;
                                cashRegister.openingTime = null;
                                cashRegister.closingAmount = null;
                                cashRegister.closingTime = null;
                                cashRegister.note = null;
                                cashRegister.showItemCollapse = false;
                            });
                            instance.cashRegisterList = tempData;
                        }

                        if (instance.tempBranchID) {
                            let autoSelectCashRegister = _.filter(this.cashRegisterList, function (cashRegister) {
                                if (cashRegister.status == 'open' && _.includes(cashRegister.userID, instance.user.id.toString())) {

                                    return cashRegister;
                                }
                            });
                            if (!_.isEmpty(autoSelectCashRegister)) {
                                instance.selectedCashRegisterID = autoSelectCashRegister[0].id;
                                instance.selectedCashRegisterName = autoSelectCashRegister[0].title;
                                instance.selectedCashRegisterBranchID = autoSelectCashRegister[0].branchID;
                                $('#branch-or-cash-register-select-modal').modal('hide');
                            }
                            instance.tempBranchID = null;
                        }
                        instance.getExpectedAmount(instance.selectedCashRegisterID);
                        instance.hideCashRegisterPreLoader = true;
                    })
                ;
            },
            checkCashRegisterOpenByUser(cashRegister) {
                let instance = this;
                if (cashRegister.status == 'open') {
                    if (instance.user.is_admin == 1) {
                        return false;
                    } else {
                        let test = _.includes(cashRegister.userID, instance.user.id.toString());
                        let check = test ? true : false;
                        return check;
                    }
                } else {
                    return true;
                }
            },
            checkCashRegisterOpen() {
                let instance = this, status;
                instance.cashRegisterList.forEach(function (cashRegister, index) {
                    if (cashRegister.status === 'open' && cashRegister.open_user_id === instance.user.id) {
                        status = true
                    }
                });
                if (status) {
                    return true;
                } else {
                    return false;
                }
            },
            setCashRegisterData: function (cashRegister, status) {
                this.$validator.validateAll().then((result) => {
                    if (result || status === 'enroll'
                    ) {
                        this.disableCloseButton = false;
                        let cashRegisterData = cashRegister,
                            instance = this,
                            flag = 0;
                        if (status == 'open') {
                            if (this.checkCashRegisterOpen()) {
                                flag = 1;
                                this.showSuccessAlert(this.trans('lang.please_close_the_current_cash_register_to_continue'));
                            } else {
                                cashRegisterData.status = 'open';
                                cashRegisterData.openingTime = moment().format('YYYY-MM-DD H:mm');
                                $('#branch-or-cash-register-select-modal').modal('hide');
                            }
                        } else if (status == 'close') {
                            cashRegisterData.closingAmount = this.closingAmount;
                            cashRegisterData.note = this.note;
                            cashRegisterData.status = 'closed';
                            cashRegisterData.closingTime = moment().format('YYYY-MM-DD H:mm');
                            this.hideCashRegisterPreLoader = false;
                        } else {
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                        if (flag === 0) {
                            this.selectedCashRegisterID = cashRegisterData.id;
                            this.selectedCashRegisterName = cashRegisterData.title;
                            this.selectedCashRegisterBranchID = cashRegisterData.branchID;
                            let instance = this;
                            instance.axiosGETorPOST(
                                {
                                    url: '/cash-register-open-close',
                                    postData: cashRegisterData,
                                },
                                (success, responseData) => {
                                    if (success && (status == 'close' || status == 'open')
                                    ) {
                                        instance.getCashRegisterData();
                                        if (status == 'close') {
                                            instance.selectedCashRegisterID = null;
                                            instance.selectedCashRegisterName = null;
                                        }
                                    }
                                }
                            );
                        }
                    }
                });
            },
            dashboard() {
                let instance = this;
                instance.redirect('/dashboard');
            },
            newCustomer(customerId) {
                this.newCustomerId = customerId;
                this.getCustomerData();
            },


        }
    }
</script>

