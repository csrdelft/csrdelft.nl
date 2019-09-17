<template>
	<div>
		<TableFieldHeader :cols="cols"></TableFieldHeader>
		<TableFieldRow :entity="entity" :cols="cols" :key="i" v-for="(entity, i) in data"></TableFieldRow>
		<TableFieldFooter :cols="cols"></TableFieldFooter>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import TableFieldFooter from './table/TableFieldFooter.vue';
	import TableFieldHeader from './table/TableFieldHeader.vue';
	import TableFieldRow from './table/TableFieldRow.vue';

	@Component({
		components: {TableFieldRow, TableFieldHeader, TableFieldFooter},
	})
	export default class TableField extends Vue {
		@Prop({
			required: true,
		})
		private data: any[];

		@Prop()
		private cols: any[];
	}
</script>

<style scoped>

</style>
