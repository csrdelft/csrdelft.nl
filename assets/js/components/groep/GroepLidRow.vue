<template>
  <!-- eslint-disable vue/no-v-html -->
  <tr>
    <td class="text-nowrap" v-html="lid.link" />
    <td
      v-for="keuze in keuzes"
      :key="keuze.naam"
    >
      <Icon v-if="getLidKeuze(keuze) === undefined" icon="ban"></Icon>
      <Icon v-else-if="keuze.type === GroepKeuzeType.CHECKBOX && getLidKeuze(keuze).selectie" icon="check"></Icon>
      <Icon v-else-if="keuze.type === GroepKeuzeType.CHECKBOX && !getLidKeuze(keuze).selectie" icon="xmark"></Icon>
      <span v-else v-html="htmlEncode(getLidKeuze(keuze).selectie)" />
    </td>
  </tr>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import GroepKeuzeType from '../../enum/GroepKeuzeType';
import { htmlEncode } from '../../lib/util';
import { GroepLid, KeuzeOptie } from '../../model/groep';
import Icon from '../common/Icon.vue';

@Component({})
export default class GroepLidRow extends Vue {
  @Prop()
  lid: GroepLid;

  @Prop()
  keuzes: KeuzeOptie[];

  private getLidKeuze(keuze: KeuzeOptie) {
    return this.lid.opmerking2.find((k) => k.naam === keuze.naam);
  }
}
</script>

<style scoped></style>
