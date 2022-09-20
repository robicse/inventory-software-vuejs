<template>
    <button class="btn btn-info app-color mobile-btn" type="submit" @click.prevent="submit()" :disabled="isDisabled" >
        <div class="float-left" v-if="buttonLoader"> <roller-loader></roller-loader></div>
        <div :class="{buttonText:buttonLoader}">{{ trans('lang.'+buttonText) }}</div>
    </button>
</template>
<script>
    export default {
        props:['buttonLoader','isDisabled','isActiveText','buttonText'],
        data() {
            return {
            }
        },
        mounted(){

        },
        methods:{
            submit(){
                let instance=this;
                instance.$emit('submit');
            },
        }
    }
</script>
