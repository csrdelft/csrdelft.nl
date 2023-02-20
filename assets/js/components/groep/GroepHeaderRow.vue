<template>
  <thead>
    <tr>
      <th scope="col" />
      <th scope="col" v-for="keuze in keuzes" :key="keuze.description">
        {{ keuze.description }}
      </th>
    </tr>
  </thead>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { KeuzeOptie } from '../../model/groep';

export default defineComponent({
  props: {
    keuzes: {
      required: true,
      type: Array as PropType<KeuzeOptie[]>,
    },
  },
});
</script>

<style scoped></style>
