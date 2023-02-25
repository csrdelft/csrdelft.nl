<template>
  <div class="mb-3">
    <label class="w-100">{{ keuze.description }}</label>
    <div class="btn-group">
      <label
        v-for="(optie, i) in keuze.opties"
        :key="i"
        :class="{
          'btn btn-outline-primary': true,
          active: optie === modelValue,
        }"
      >
        <input
          type="radio"
          :name="'MultiSelectKeuze-' + keuze.naam"
          class="d-none"
          :value="optie"
          :checked="optie === modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
        />{{ optie }}
      </label>
    </div>
  </div>
</template>

<script lang="ts">
import { KeuzeOptie } from '../../../model/groep';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

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
