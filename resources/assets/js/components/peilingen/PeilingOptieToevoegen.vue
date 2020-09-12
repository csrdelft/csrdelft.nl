<template>
  <div>
    <a @click="toevoegen">
      <span :class="icon" /> {{ text }}
    </a>
  </div>
</template>

<script lang="ts">
	import axios from 'axios';
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import {domUpdate} from '../../lib/domUpdate';

	@Component
	export default class PeilingOptieToevoegen extends Vue {
		icon = 'ico add';
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
			this.icon = 'ico arrow_rotate_clockwise rotating';
			axios.post(this.optieToevoegenUrl.toString())
				.then((response) => {
					domUpdate(response.data);
					this.icon = 'ico add';
				})
				.catch(() => {
					this.icon = 'ico cancel';
					this.text = 'Mag geen optie meer toevoegen';
				});
		}
	}
</script>

<style scoped>

</style>
