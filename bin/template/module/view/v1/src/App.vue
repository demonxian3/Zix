<template>
  <div id="app">
    <h1 class='title'> welcome to using <strong>ZIX</strong> framework!</h1>
    <keep-alive>
      <router-view></router-view>
    </keep-alive>
  </div>
</template>

<script>
export default {
  name: "app",
  data() {
    return {
    };
  },
  methods: {
  }
};
</script>

<style>
.title{
  text-align: center;
  color: white;
  line-height: 700px;
  text-shadow:5px 2px 6px #000;
}
</style>
