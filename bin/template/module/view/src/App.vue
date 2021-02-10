<template>
  <div id="app">
    <title>title</title>
    <keep-alive>
      <router-view v-if="$route.meta.keepAlive"></router-view>
    </keep-alive>
    <router-view v-if="!$route.meta.keepAlive"></router-view>
  </div>
</template>

<script>
export default {
  name: "app",
  data() {
    return {

    }
  },
  created() {
    // 等待plus ready后再调用5+ API：
    document.addEventListener("plusready", () => {
      // 禁用屏幕旋转
      plus.screen.lockOrientation("portrait-primary"); 
      let first = 0;
      plus.key.addEventListener(
        "backbutton",
        () => {
          if (!first) {
            if (this.$route.meta.canback) {
              this.$router.back();
            } else {
              plus.nativeUI.toast("已是底页了，双击返回退出");
              first = new Date().getTime();
              setTimeout(function() {
                first = 0;
              }, 800);
            }
          } else {
            //获取第二次点击的时间戳, 两次之差 小于 1000ms 说明1s点击了两次,
            if (new Date().getTime() - first < 800) {
              plus.runtime.quit(); //退出应用
            }
          }
        },
        false
      );
    });
  },
  methods: {
  }
};
</script>

<style>
</style>
