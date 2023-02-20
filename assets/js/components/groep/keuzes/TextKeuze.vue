<template>
  <div class="mb-3">
    <label :for="'customCheck' + keuze.naam">{{ keuze.description }}</label>
    <input
      :id="'customCheck' + keuze.naam"
      type="text"
      class="form-control"
      :value="value"
      @input="$emit('input', $event.target.value)"
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
    value: {
      required: true,
      type: String,
    },
  },
});
</script>

<style scoped></style>
