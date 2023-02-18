<template>
  <!-- eslint-disable vue/no-v-html -->
  <tr>
    <td class="text-nowrap" v-html="lid.link" />
    <td v-for="keuze in keuzes" :key="keuze.naam">
      <Icon v-if="getLidKeuze(keuze) === undefined" icon="ban"></Icon>
      <Icon
        v-else-if="isKeuzeCheckbox(keuze) && getLidKeuze(keuze).selectie"
        icon="check"
      ></Icon>
      <Icon
        v-else-if="isKeuzeCheckbox(keuze) && !getLidKeuze(keuze).selectie"
        icon="xmark"
      ></Icon>
      <span v-else v-html="renderSelectie(getLidKeuze(keuze))" />
    </td>
  </tr>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue';
import GroepKeuzeType from '../../enum/GroepKeuzeType';
import { htmlEncode } from '../../lib/util';
import { GroepKeuzeSelectie, GroepLid, KeuzeOptie } from '../../model/groep';
import Icon from '../common/Icon.vue';

export default Vue.extend({
  components: { Icon },
  props: {
    lid: {
      required: true,
      type: Object as PropType<GroepLid>,
    },
    keuzes: {
      required: true,
      type: Array as PropType<KeuzeOptie[]>,
    },
  },
  methods: {
    getLidKeuze(keuze: KeuzeOptie) {
      return this.lid.opmerking2
        ? this.lid.opmerking2.find((k) => k.naam === keuze.naam)
        : undefined;
    },
    isKeuzeCheckbox(keuze: KeuzeOptie) {
      return keuze.type === GroepKeuzeType.CHECKBOX;
    },
    renderSelectie(lidKeuze: GroepKeuzeSelectie) {
      return htmlEncode(lidKeuze.selectie);
    },
  },
});
</script>

<style scoped></style>
