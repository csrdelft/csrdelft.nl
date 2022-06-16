<template>
  <div>
    <a @click="toevoegen"> <Icon :icon="icon" /> {{ text }} </a>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { domUpdate } from '../../lib/domUpdate';
import Icon from '../common/Icon.vue';

@Component({
  components: { Icon },
})
export default class PeilingOptieToevoegen extends Vue {
  icon = 'plus';
  text = 'Optie toevoegen';

  @Prop({
    type: Number,
  })
  id: number;

  private get optieToevoegenUrl() {
    return `/peilingen/opties/${this.id}/toevoegen`;
  }

  private toevoegen(event: MouseEvent) {
    event.preventDefault();
    this.icon = 'spinner fa-spin';
    axios
      .post(this.optieToevoegenUrl.toString())
      .then((response) => {
        domUpdate(response.data);
        this.icon = 'plus';
      })
      .catch(() => {
        this.icon = 'ban';
        this.text = 'Mag geen optie meer toevoegen';
      });
  }
}
</script>

<style scoped></style>
