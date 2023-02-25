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
          :checked="modelValue"
          @change="$emit('update:modelValue', $event.target.checked)"
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
import { defineComponent } from 'vue';
import { init } from '../../ctx';
import ProgressBar from '../common/ProgressBar.vue';

export default defineComponent({
  components: {
    ProgressBar,
  },
  props: {
    id: {
      default: 0,
      type: Number,
    },
    peilingId: {
      default: 0,
      type: Number,
    },
    titel: {
      default: '',
      type: String,
    },
    beschrijving: {
      default: '',
      type: String,
    },
    stemmen: {
      default: 0,
      type: Number,
    },
    magStemmen: Boolean,
    aantalGestemd: {
      default: 0,
      type: Number,
    },
    heeftGestemd: Boolean,
    keuzesOver: Boolean,
    modelValue: Boolean,
  },
  emits: ['update:modelValue'],
  computed: {
    kanStemmen() {
      return this.magStemmen && !this.heeftGestemd;
    },
    progress() {
      return ((this.stemmen / this.aantalGestemd) * 100).toFixed(2);
    },
    progressText() {
      return `${this.progress}% (${this.stemmen})`;
    },
    isDisabled() {
      return !this.modelValue && !this.keuzesOver;
    },
  },
  watch: {
    kanStemmen() {
      this.initBeschrijvingContext();
    },
  },
  mounted() {
    this.initBeschrijvingContext();
  },
  methods: {
    initBeschrijvingContext() {
      setTimeout(() => {
        if (this.kanStemmen) {
          init(this.$refs.beschrijving as HTMLElement);
        } else {
          init(this.$refs.beschrijving_gestemd as HTMLElement);
        }
      });
    },
  },
});
</script>

<style scoped></style>
