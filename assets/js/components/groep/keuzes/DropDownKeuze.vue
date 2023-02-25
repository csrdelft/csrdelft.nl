<template>
  <div class="mb-3">
    <label class="w-100"
      >{{ keuze.description }}
      <select
        class="form-select"
        :name="'DropDownKeuze-' + keuze.naam"
        @input="$emit('update:modelValue', $event.target.value)"
      >
        <option
          v-for="(optie, i) in keuze.opties"
          :key="i"
          :value="optie"
          :selected="optie === modelValue"
        >
          {{ optie }}
        </option>
      </select>
    </label>
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
    modelValue: {
      required: true,
      type: String,
    },
  },
  emits: ['update:modelValue'],
});
</script>

<style scoped></style>
