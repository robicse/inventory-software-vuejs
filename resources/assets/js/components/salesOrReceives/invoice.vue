<template>
    <div style="display:none">
        <div id="cart-print" style="padding: 20px !important; color: #444d55; font-family: 'Raleway', sans-serif;">
            <span v-html="HTMLcontent"></span>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,

        props: [
            'printInvoice',
            'HTMLcontent'
        ],
        watch: {
            printInvoice: function (newVal) {
                if (newVal) {
                    this.printReceipt();
                }
            }
        },
        methods: {
            printReceipt() {
                $('#cart-print').printThis({
                    importCSS: false,
                    importStyle: true,
                    printContainer: true,
                    header: null,
                });
                this.$emit('resetGetInvoice', false);
            },
        },
    }
</script>