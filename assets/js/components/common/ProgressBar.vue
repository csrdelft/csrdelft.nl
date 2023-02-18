<template>
  <div class="progress">
    <div class="progress-bar" :style="{ width: progress + '%' }">
      <slot />
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend({
  props: {
    progress: {
      default: 0,
      type: [Number, String],
    },
  },
});
</script>

<style scoped></style>
