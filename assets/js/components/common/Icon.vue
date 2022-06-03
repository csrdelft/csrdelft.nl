<template>
	<span :class="iconClass" />
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';

@Component
export default class Icon extends Vue {
	@Prop()
	icon: string;

	private get iconClass() {
		return `ico ${this.icon}`;
	}
}
</script>

<style scoped></style>
