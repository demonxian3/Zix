<script src="../../../../../zix/bin/template/module/view/webpack.config.js"></script>
<template>
  <div class="w-100 p-5 text-center" id="home">
    <img src="@/assets/img/login.png" class="border m-auto">
    <div>{{msg}}</div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        msg: 'Hello world',
      };
    },
    beforeCreate(){

    },
    created() {
      console.log(this.icon);
    },
    methods:{
    }
  }
</script>

<style>
</style>
