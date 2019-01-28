<template>
	<span :class="iconClass"></span>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';

	@Component
	export default class Icon extends Vue {
		@Prop()
		private icon: string;

		get iconClass() {
			return `ico ${this.icon}`;
		}
	}
</script>

<style scoped>

</style>
