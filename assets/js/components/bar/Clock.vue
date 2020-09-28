<template>
	<div>
		{{clock}}
	</div>
</template>
<script lang="ts">
	import moment from 'moment';
	import Vue from 'vue';
	import {Component} from 'vue-property-decorator';

	@Component
	export default class Clock extends Vue {
		public static getClock(): string {
			return moment().format('hh:mm:ss');
		}

		public clock: string = '';

		public interval: NodeJS.Timeout | undefined;

		public created(): void {
			this.updateClock();
			this.interval = setInterval(this.updateClock, 1000);
		}

		public destroyed(): void {
			if (this.interval !== undefined) {
				clearInterval(this.interval);
			}
		}

		private updateClock(): void {
			this.clock = Clock.getClock();
		}
	}
</script>
<style scoped>
	div {
		line-height: 74px;
		font-size: 20px;
		padding: 0 15px 0 0;
		font-weight: bold;
		color: #000;
	}
</style>
