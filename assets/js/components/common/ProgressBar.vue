<template>
  <div class="progress">
    <div class="progress-bar" :style="{ width: progress + '%' }">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ progress: number | string }>();
</script>

<style scoped></style>
