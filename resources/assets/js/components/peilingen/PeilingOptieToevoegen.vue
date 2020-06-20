<template>
	<div>
		<a @click="toevoegen">
			<span :class="icon"></span> {{text}}
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
		private icon = 'ico add';
		private text = 'Optie toevoegen';

		@Prop({
			type: Number,
		})
		private id: number;

		protected get optieToevoegenUrl() {
			return `/peilingen/opties/${this.id}/toevoegen`;
		}

		protected toevoegen(event) {
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
