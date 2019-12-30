<template>
  <div class="form-group" v-show='show'>
    <label :class="fontClass">
      {{label}}
    </label>
    <slot></slot>
    <small :class="fontClass">{{tip}}</small>
  </div>
</template>

<script>
export default {
  props:['label', 'tip', 'color'],
  created(){
      this.show = true;
      this.fontClass = '';
      if (this.hidden) this.show = false;
      if (this.color) this.fontClass = ' text-' + this.color;
      
  }
};
</script>

<style>
</style>