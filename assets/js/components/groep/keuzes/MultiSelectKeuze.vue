<template>
  <div class="mb-3">
    <label class="w-100">{{ keuze.description }}</label>
    <div class="btn-group">
      <label
        v-for="(optie, i) in keuze.opties"
        :key="i"
        :class="{ 'btn btn-outline-primary': true, active: optie === value }"
      >
        <input
          type="radio"
          :name="'MultiSelectKeuze-' + keuze.naam"
          class="d-none"
          :value="optie"
          :checked="optie === value"
          @input="$emit('input', $event.target.value)"
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
    value: {
      required: true,
      type: String,
    },
  },
});
</script>

<style scoped></style>
