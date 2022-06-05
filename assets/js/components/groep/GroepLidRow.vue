<template>
  <!-- eslint-disable vue/no-v-html -->
  <tr>
    <td
      class="text-nowrap"
      v-html="lid.link"
    />
    <td
      v-for="keuze in keuzes"
      :key="keuze.naam"
      v-html="renderSelectie(keuze)"
    />
  </tr>
</template>

<script lang="ts">
import Vue from 'vue';
import {Component, Prop} from 'vue-property-decorator';
import GroepKeuzeType from '../../enum/GroepKeuzeType';
import {htmlEncode} from '../../lib/util';
import {GroepLid, KeuzeOptie} from '../../model/groep';
import Icon from '../common/Icon.vue';

@Component({})
export default class GroepLidRow extends Vue {
  @Prop()
  lid: GroepLid;

  @Prop()
  keuzes: KeuzeOptie[];

  private renderSelectie(keuze: KeuzeOptie) {
    const lidKeuze = this.lid.opmerking2.find((k) => k.naam === keuze.naam);

    if (lidKeuze === undefined) {
      return '<Icon icon="ban"></Icon>';
    }

    switch (keuze.type) {
      case GroepKeuzeType.CHECKBOX:
        return lidKeuze.selectie ? '<Icon icon="check"></Icon>' : '<Icon icon="xmark"></Icon>';
      default:
        return htmlEncode(lidKeuze.selectie);
    }
  }
}
</script>

<style scoped>

</style>

