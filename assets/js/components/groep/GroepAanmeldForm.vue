<template>
  <div>
    <component
      :is="getComponent(opmerking.naam)"
      v-for="opmerking in opmerkingData"
      :key="opmerking.naam"
      v-model="opmerking.selectie"
      :keuze="getKeuze(opmerking.naam)"
    />
    <button class="btn btn-primary" @click="$emit('aanmelden', opmerkingData)">
      Aanmelden
    </button>
  </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import GroepKeuzeType from '../../enum/GroepKeuzeType';
import { GroepKeuzeSelectie, KeuzeOptie } from '../../model/groep';
import CheckboxKeuze from './keuzes/CheckboxKeuze.vue';
import DropDownKeuze from './keuzes/DropDownKeuze.vue';
import MultiSelectKeuze from './keuzes/MultiSelectKeuze.vue';
import TextKeuze from './keuzes/TextKeuze.vue';

export default defineComponent({
  props: {
    keuzes: {
      required: true,
      type: Array as PropType<KeuzeOptie[]>,
    },
    opmerking: {
      required: true,
      type: Array as PropType<GroepKeuzeSelectie[]>,
    },
    aangemeld: {
      required: true,
      type: Boolean,
    },
  },
  data: () => ({
    opmerkingData: [] as GroepKeuzeSelectie[],
  }),
  created() {
    this.opmerkingData = this.opmerking;
  },
  methods: {
    getKeuze(naam: string) {
      return this.keuzes.find((keuze) => keuze.naam === naam);
    },
    getComponent(naam: string) {
      const type = this.getKeuze(naam).type;
      switch (type) {
        case GroepKeuzeType.CHECKBOX:
          return CheckboxKeuze;
        case GroepKeuzeType.TEXT:
          return TextKeuze;
        case GroepKeuzeType.RADIOS:
          return MultiSelectKeuze;
        case GroepKeuzeType.DROPDOWN:
          return DropDownKeuze;
        default:
          throw Error(
            `Kan component voor GroepKeuzeType '${type}' niet vinden.`
          );
      }
    },
  },
});
</script>

<style scoped></style>
