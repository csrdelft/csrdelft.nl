<template>
  <!-- eslint-disable vue/no-v-html -->
  <div v-if="!kanStemmen" class="row">
    <div class="col-md-4">
      {{ titel }}
    </div>
    <div class="col-md-6">
      <ProgressBar :progress="progress" />
    </div>
    <div class="col-md-2">
      {{ progressText }}
    </div>
    <div
      ref="beschrijving_gestemd"
      class="col text-muted pt-2"
      v-html="beschrijving"
    />
  </div>
  <div v-else class="row">
    <div class="col-md-12">
      <div class="form-check">
        <input
          :id="'PeilingOptie' + id"
          type="checkbox"
          class="form-check-input"
          name="optie"
          :value="id"
          :disabled="isDisabled"
          :checked="selected"
          @change="$emit('input', $event.target.checked)"
        />
        <label :for="'PeilingOptie' + id" class="form-check-label">{{
          titel
        }}</label>
      </div>
    </div>
    <div ref="beschrijving" class="col-md-12 pt-2" v-html="beschrijving" />
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop, Watch } from 'vue-property-decorator';
import { init } from '../../ctx';
import ProgressBar from '../common/ProgressBar.vue';

@Component({
  components: { ProgressBar },
})
export default class PeilingOptie extends Vue {
  @Prop()
  id: string;
  @Prop()
  peilingId: number;
  @Prop()
  titel: string;
  @Prop()
  beschrijving: string;
  @Prop()
  stemmen: number;
  @Prop()
  magStemmen: boolean;
  @Prop()
  aantalGestemd: number;
  @Prop()
  heeftGestemd: boolean;
  @Prop()
  keuzesOver: boolean;
  @Prop()
  selected: boolean;

  private mounted() {
    this.initBeschrijvingContext();
  }

  @Watch('kanStemmen')
  private initBeschrijvingContext() {
    setTimeout(() => {
      if (this.kanStemmen) {
        init(this.$refs.beschrijving as HTMLElement);
      } else {
        init(this.$refs.beschrijving_gestemd as HTMLElement);
      }
    });
  }

  private get kanStemmen() {
    return this.magStemmen && !this.heeftGestemd;
  }

  private get progress() {
    return ((this.stemmen / this.aantalGestemd) * 100).toFixed(2);
  }

  private get progressText() {
    return `${this.progress}% (${this.stemmen})`;
  }

  private get isDisabled() {
    return !this.selected && !this.keuzesOver;
  }
}
</script>

<style scoped></style>
