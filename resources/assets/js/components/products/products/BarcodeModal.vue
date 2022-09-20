<template>
    <div>
        <div class="modal fade" id="barcode-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="container-fluid p-0">
                            <div class="row">
                                <div class="col-10">
                                    <h5 class="m-0">{{trans('lang.print_barcode')}}</h5>
                                </div>
                                <div class="col-2 text-right">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                                        <span aria-hidden="true"><i class="la la-close"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-contents app-bg-color p-3">
                        <div class="bg-white rounded p-3">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="nav-item active">
                                    <a class="nav-link active" data-toggle="tab" href="#home" @click="unCheck()">{{trans('lang.all_product')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#choose_product" @click="addSetting()">{{trans('lang.choose_product')}}</a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div id="home" class="tab-pane fade show active">

                                </div>
                                <div id="choose_product" class="tab-pane fade"><br>
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" @click='checkAll()' v-model='isCheckAll'></th>
                                                <th>{{trans('lang.title')}}</th>
                                            <th>{{trans('lang.group')}}</th>
                                            <th>{{trans('lang.brand')}}</th>
                                            <th>{{trans('lang.category')}}</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <tr v-for="(barcodedata,index) in barcodeData">
                                            <td><input type="checkbox" :value='barcodedata' v-model='data' @click='updateCheckall()'></td>
                                            <td>{{barcodedata.title}}</td>
                                            <td>{{barcodedata.group_name}}</td>
                                            <td>{{barcodedata.brand_name}}</td>
                                            <td>{{barcodedata.category_name}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                        <p>{{trans('lang.settings')}}</p>
                                    <hr>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_columns')}}</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1" @click='column(1)' checked>
                                            <label class="form-check-label" for="inlineRadio1">1</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2" @click='column(2)'>
                                            <label class="form-check-label" for="inlineRadio2">2</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="option3" @click='column(3)'>
                                            <label class="form-check-label" for="inlineRadio3">3</label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_copies')}}</label>
                                        <select class="col-sm-6" id="sel1" v-model="copies" >
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>4</option>
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_rows_in_every_pages')}}</label>
                                        <select class="col-sm-6" id="sel" v-model="totalRows" >
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary app-color" data-dismiss="modal" aria-label="Close" @click="closeModal">
                                {{ trans('lang.cancel') }}
                            </button>
                            <button class="btn btn-primary app-color mobile-btn"
                                    @click="previewBarcode()">
                                {{trans('lang.preview')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Barcode preview modal -->
        <barcode-preview-modal v-if="isBarcodePreviewModalActive"
                               :data="data"
                               :totalColumns="totalColumns"
                               :totalRows="totalRows"
                               :copies="copies"
                               @resetImport="resetImport"
                               @barcodeModalActive="barcodeModalActive">
        </barcode-preview-modal>
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
        props: ['barcodeData',],
        data() {
            return {
                isCheckAll:true,
                isSetting:false,
                data: [],
                totalColumns: '1',
                totalRows: '4',
                copies: '1',
                isBarcodePreviewModalActive: false,
                isClicked:true,
            }
        },
        mounted() {
            let instance = this;
            $('#barcode-modal').on('hidden.bs.modal', function () {
                if(instance.isClicked){
                   instance.closeModal();
                }
            });
            this.unCheck();
        },
        methods:
            {
              checkAll(){
                    this.isCheckAll = !this.isCheckAll;
                    this.data = [];
                    if (this.isCheckAll) { // Check all
                        for (var key in this.barcodeData) {
                            this.data.push(this.barcodeData[key]);
                        }
                    }
                },
                updateCheckall() {
                  if (this.data.length == this.barcodeData.length) {
                       this.isCheckAll = false;
                    }
                },
                unCheck(){
                    this.data = this.barcodeData;
                    this.isCheckAll = true;
                },
                previewBarcode(){
                    this.isClicked= false;
                    let instance = this;
                    instance.isBarcodePreviewModalActive = true;
                    setTimeout(function () {
                        $('#barcode-preview-modal').modal('show');
                    });
                    $('#barcode-modal').modal('hide');
                },
                emptyCheckData() {
                    this.data = [];
                },
                column: function (val) {
                    this.totalColumns = val;
                },
                resetModal(){
                    this.$emit('resetModal');
                    this.isBarcodePreviewModalActive = false;
                },
                closeModal() {
                    this.$emit('resetModal');
                },
                addSetting(){
                    this.isSetting = true;
                },
                resetImport(){
                    this.$emit('resetImport');
                },
               barcodeModalActive(){
                 this.isBarcodePreviewModalActive = true;
            },
        }
    }
</script>

