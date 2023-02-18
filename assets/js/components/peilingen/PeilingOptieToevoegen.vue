<template>
  <div>
    <a @click="toevoegen">
      <Icon :icon="icon" />
      {{ text }}
    </a>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import Vue from 'vue';
import { domUpdate } from '../../lib/domUpdate';
import Icon from '../common/Icon.vue';

export default Vue.extend({
  components: { Icon },
  props: {
    id: {
      default: 0,
      type: Number,
    },
  },
  data: () => ({
    icon: 'plus',
    text: 'Optie toevoegen',
  }),
  computed: {
    optieToevoegenUrl() {
      return `/peilingen/opties/${this.id}/toevoegen`;
    },
  },
  methods: {
    toevoegen(event: MouseEvent) {
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
    },
  },
});
</script>

<style scoped></style>
