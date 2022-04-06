<template>
  <i :class="iconClass"></i>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';

@Component
export default class Icon extends Vue {
  @Prop()
  icon: string;

  private get iconClass() {
    return `fas fa-${this.icon}`;
  }
}
</script>

<style scoped></style>
