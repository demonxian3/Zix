<template>
  <div class="card-header bg-light fixed-top d-flex justify-content-between d-flex align-items-center">
      <i class="fa fa-arrow-left border-0 return-icon text-info" @click="$router.back()"></i>
      <div class='text-center ml-4'>{{mtext}}</div>
      <button class="btn btn-outline-success btn-sm" @click="$router.replace(rlink)">{{rtext}}</button>
  </div> 
</template>

<script>
export default {
  props: ["rtext", "mtext", "rlink", "llink"]
};
</script>

<style scoped>
.return-icon {
	font-size:23px;
	line-height:2.4rem;
}
</style>