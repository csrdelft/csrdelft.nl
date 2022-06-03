<template>
	<div class="progress">
		<div class="progress-bar" :style="{ width: progress + '%' }">
			<slot />
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';

@Component
export default class ProgressBar extends Vue {
	@Prop({
		type: [Number, String],
	})
	progress: number | string; // Liever een nummer, maar een string kunnen we ook mee overweg.
}
</script>

<style scoped></style>
