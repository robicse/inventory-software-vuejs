<template>
    <div>
        <div class="modal fade" id="barcode-preview-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="container-fluid p-0">
                            <div class="row">
                                <div class="col-10">
                                    <h5 class="m-0">{{trans('lang.barcode_preview')}}</h5>
                                </div>
                                <div class="col-2 text-right">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                            @click="closePreviewModal">
                                        <span aria-hidden="true"><i class="la la-close"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="barcode" class="modal-body app-bg-color p-3">
                        <div class="bg-white rounded p-3">
                            <div class="page full-a4" v-for="(barcode,index) in newBarcode">
                                <div class="row equal">
                                    <div class="mb-4"
                                         :class="[(totalColumns==1?'col-12':''),(totalColumns==2?'col-6':''),(totalColumns==3?'col-4':'')]"
                                         v-for="(variant,index) in barcode">
                                        <div class="barcode-container">
                                            <h4 style="margin: 0; padding: 0;">{{trans('lang.app_title')}}</h4>
                                            <p class="limit-title" style="margin: 0; padding: 0 3px;">{{variant.title}}</p>
                                            <p class="barcode-variant-title" style="margin: 0; padding: 0;"
                                               v-if="variant.variant_title !== 'default_variant'">
                                                {{trans('lang.variant_title')}} :{{variant.variant_title }}

                                            </p>
                                            <barcode format="CODE39"
                                                     :width="variant.newBarcode.toString().length > 3 ? '1' : '2'"
                                                     :value="variant.newBarcode"
                                                     font-options="bold"
                                                     textPosition="bottom"
                                                     displayValue="true"
                                                     style="margin: 0; padding: 0;"
                                                     display-value=false>
                                            </barcode>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary app-color" @click="closePreviewModal">
                            {{ trans('lang.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary app-color" @click="print()">
                            {{ trans('lang.print') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    import VueBarcode from 'vue-barcode';

    export default {
        extends: axiosGetPost,
        components: {
            'barcode': VueBarcode
        },
        props: ['data', 'totalColumns', 'totalRows', 'copies'],
        data() {
            return {

            }
        },
        computed: {
            newBarcode: function () {
                let checkedItem = [], i, count = this.copies, barcodePerPage = this.totalColumns * this.totalRows,
                    index, newArray, newBarcode = [];
                this.data.forEach(function (element) {
                    element.variants.forEach(function (item) {
                        item.title = element.title;
                        for (i = 0; i < count; i++) {
                            checkedItem.push(item);
                        }
                    });
                });

                for (index = 0; index <= checkedItem.length; index += barcodePerPage) {
                    newArray = checkedItem.filter(function (element, i) {
                        return index <= i && i < index + barcodePerPage;
                    });
                    newBarcode.push(newArray);
                }
                return newBarcode;
            }
        },
        mounted() {
            let instance = this;
            $('#barcode-preview-modal').on('hidden.bs.modal', function () {
                instance.closePreviewModal();
            });

            // Limit Product Title
            let maximum = 15;
            let total, string;
            $('.limit-title').each(function() {
                string = String($(this).html());
                total = string.length;
                string = (total <= maximum)
                    ? string
                    : string.substring(0,(maximum + 1))+"...";
                $(this).html(string);
            });
        },
        methods:
            {
                closePreviewModal() {
                    this.$emit('resetImport');
                    $('#barcode-preview-modal').modal('hide');
                },
                print() {
                    $('#barcode').printThis({
                        importCSS: true,
                        importStyle: true,
                        printContainer: true,
                        header: null,
                    });
                    this.$emit('resetGetInvoice', false);
                },
            },
    }
</script>
