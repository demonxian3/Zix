<template>
  <b-card no-body class="mb-1">
      <b-button block href="#" @click="toggle()" :variant="theme">
        <span class='fa' :class="'fa-'+fa" v-if='fa'></span>
        {{title}}
      </b-button>
    <b-collapse :ref='id' :visible='visible' role="tabpanel">
        <slot></slot>
    </b-collapse>
  </b-card>
</template>

<script>
export default {
  props: ["fa", "title", 'id', 'theme'],
  data(){
    return {
      visible: false,
    }
  },
  methods:{
    toggle(){
      this.visible = !this.visible;
    }
  }
};
</script>
