<template>
	<div>
		<div :key="i" v-for="(col, i) in cols">
			<span>{{i}} {{entity[i]}}</span>
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';

	@Component
	export default class TableFieldRow extends Vue {
		@Prop()
		private cols: any;
		@Prop()
		private entity: any;
	}
</script>

<style scoped>

</style>
