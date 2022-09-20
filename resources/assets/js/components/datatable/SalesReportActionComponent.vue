<template>
    <div v-if="rowData.invoice_id != 'Total' && rowData.invoice_id != 'Grand Total' && rowData.id != 'Total' && rowData.id != 'Grand Total'" class="action-button-wrapper">
        <div class='action-button-container'>
            
            <!-- Due Paid -->
            <!-- <a href="" class='action-button' data-toggle="modal"
               data-target="#due-amount-edit-modal"
               @click.prevent="viewCustomerEdit(rowData,rowIndex)">
                <i class="la la-money la-2x"></i>
            </a> -->

            <!-- Edit Receives OR Sales Items -->
            <a href="" class='action-button' data-toggle="modal"
               data-target="#receive-sale-edit-modal"
               @click.prevent="viewCustomerEdit(rowData,rowIndex)">
                <i class="la la-money la-2x"></i>
            </a>

            <a href="" class='action-button'  data-toggle="modal" 
                data-target="#confirm-delete" 
                @click.prevent="selectedDeletableId(rowData.id,rowIndex)">
                <i class="la la-trash-o la-2x"></i>
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
            selectedRowData : this.rowData,
            due : unformat(this.rowData.due_amount)
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

        /* Due Paid*/
        /*viewCustomerEdit(rowData){
            this.$hub.$emit('viewSalesReportEdit', rowData);
        },*/
        /*Receive Sale Edit*/
         viewCustomerEdit(rowData){
            this.$hub.$emit('viewSalesReportEdit', rowData);
        },
        selectedDeletableId(id,index) {
            this.$hub.$emit('selectedDeletableId', id, index);
        }
    }
}
</script>
