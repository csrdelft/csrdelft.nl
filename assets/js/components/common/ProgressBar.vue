<template>
  <div class="progress">
    <div class="progress-bar" :style="{ width: progress + '%' }">
      <slot />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    progress: {
      default: 0,
      type: [Number, String],
    },
  },
});
</script>

<style scoped></style>
