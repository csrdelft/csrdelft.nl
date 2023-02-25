<template>
  <div class="mb-3">
    <div class="custom-control custom-checkbox">
      <input
        :id="`customCheck${keuze.naam}`"
        type="checkbox"
        class="custom-control-input"
        :checked="modelValue"
        @input="$emit('update:modelValue', $event.target.checked)"
      />
      <label class="custom-control-label" :for="`customCheck${keuze.naam}`">{{
        keuze.description
      }}</label>
    </div>
  </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { KeuzeOptie } from '../../../model/groep';

export default defineComponent({
  props: {
    keuze: {
      required: true,
      type: Object as PropType<KeuzeOptie>,
    },
    modelValue: Boolean,
  },
  emits: ['update:modelValue'],
});
</script>

<style scoped></style>
