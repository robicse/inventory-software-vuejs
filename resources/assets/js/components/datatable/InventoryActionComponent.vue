<template>
    <div class="action-button-wrapper">
        <div class='action-button-container'>

            <!-- Edit Receives OR Sales Items -->
            <a href="" class='action-button' data-toggle="modal"
               data-target="#inventory-edit-modal"
               @click.prevent="viewInventoryStockEdit(rowData,rowIndex)">
                <i class="la la-money la-2x"></i>
            </a>

        </div>
        <i class="la la-ellipsis-v la-1x"></i>
    </div>
</template>


<script>
    const {unformat} = require('number-currency-format');

    export default {
    props: ['rowData', 'rowIndex'],
    data(){
        return{
            selectedRowData : this.rowData
        }
    },
    mounted(){
        /*console.log('First Running And Coming..');*/
        $(".action-button-wrapper")
            .on("mouseover", function () {
                $(this).addClass("active");
            })
            .on("mouseleave", function () {
                $(this).removeClass("active");
            });
    },
    methods:{

        /*Edit*/
         viewInventoryStockEdit(rowData){
            this.$hub.$emit('viewInventoryEdit', rowData);
        },
        
    }
}
</script>
