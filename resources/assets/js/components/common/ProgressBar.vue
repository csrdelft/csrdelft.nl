<template>
	<div :class="{'progress': true, 'flex-row-reverse': reverse}">
		<div class="progress-bar" :style="{width: progress + '%'}">
			<slot/>
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';

	@Component
	export default class ProgressBar extends Vue {
		@Prop({
			type: [Number, String],
		})
		private progress: number | string; // Liever een nummer, maar een string kunnen we ook mee overweg.

		@Prop({
			default: () => false,
			required: false,
			type: Boolean,
		})
		private reverse: boolean;
	}
</script>

<style scoped>

</style>
