<template>
  <div class="mb-3">
    <label :for="'customCheck' + keuze.naam">{{ keuze.description }}</label>
    <input
      :id="'customCheck' + keuze.naam"
      type="text"
      class="form-control"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { KeuzeOptie } from '../../../model/groep';

export default defineComponent({
  props: {
    keuze: {
      required: true,
      type: Object as PropType<KeuzeOptie>,
    },
    modelValue: {
      required: true,
      type: String,
    },
  },
  emits: ['update:modelValue'],
});
</script>

<style scoped></style>
