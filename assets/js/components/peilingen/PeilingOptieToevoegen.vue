<template>
  <div>
    <a @click="toevoegen">
      <Icon :icon="data.icon" />
      {{ data.text }}
    </a>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, reactive } from 'vue';
import { domUpdate } from '../../lib/domUpdate';
import Icon from '../common/Icon.vue';

const props = defineProps<{ id: number }>();

const data = reactive({
  icon: 'plus',
  text: 'Optie toevoegen',
});

const optieToevoegenUrl = computed(
  () => `/peilingen/opties/${props.id}/toevoegen`
);

const toevoegen = (event: MouseEvent) => {
  event.preventDefault();
  data.icon = 'spinner fa-spin';
  axios
    .post(optieToevoegenUrl.value.toString())
    .then((response) => {
      domUpdate(response.data);
      data.icon = 'plus';
    })
    .catch(() => {
      data.icon = 'ban';
      data.text = 'Mag geen optie meer toevoegen';
    });
};
</script>

<style scoped></style>
