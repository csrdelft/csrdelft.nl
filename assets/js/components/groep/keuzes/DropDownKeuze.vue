<template>
  <div class="mb-3">
    <label class="w-100"
      >{{ keuze.description }}
      <select
        class="form-select"
        :name="'DropDownKeuze-' + keuze.naam"
        @input="$emit('input', $event.target.value)"
      >
        <option
          v-for="(optie, i) in keuze.opties"
          :key="i"
          :value="optie"
          :selected="optie === value"
        >
          {{ optie }}
        </option>
      </select>
    </label>
  </div>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue';
import { KeuzeOptie } from '../../../model/groep';

export default Vue.extend({
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
