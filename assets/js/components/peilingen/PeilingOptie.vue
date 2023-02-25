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
      ref="beschrijvingGestemdRef"
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
    <div ref="beschrijvingRef" class="col-md-12 pt-2" v-html="beschrijving" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { init } from '../../ctx';
import ProgressBar from '../common/ProgressBar.vue';

const props = defineProps<{
  id: number;
  peilingId: number;
  titel: string;
  beschrijving: string;
  stemmen: number;
  magStemmen: boolean;
  aantalGestemd: number;
  heeftGestemd: boolean;
  keuzesOver: boolean;
  modelValue?: boolean;
}>();

defineEmits<{
  (event: 'update:modelValue', checked: number): void;
}>();

const beschrijvingRef = ref<HTMLElement | null>(null);
const beschrijvingGestemdRef = ref<HTMLElement | null>(null);

const kanStemmen = computed(() => {
  return props.magStemmen && !props.heeftGestemd;
});
const progress = computed(() => {
  return ((props.stemmen / props.aantalGestemd) * 100).toFixed(2);
});
const progressText = computed(() => {
  return `${progress.value}% (${props.stemmen})`;
});
const isDisabled = computed(() => {
  return !props.modelValue && !props.keuzesOver;
});

watch(kanStemmen, () => {
  initBeschrijvingContext();
});

onMounted(() => initBeschrijvingContext());

const initBeschrijvingContext = () => {
  setTimeout(() => {
    if (kanStemmen.value && beschrijvingRef.value instanceof HTMLElement) {
      init(beschrijvingRef.value as HTMLElement);
    } else if (beschrijvingGestemdRef.value instanceof HTMLElement) {
      init(beschrijvingGestemdRef.value as HTMLElement);
    }
  });
};
</script>

<style scoped></style>
