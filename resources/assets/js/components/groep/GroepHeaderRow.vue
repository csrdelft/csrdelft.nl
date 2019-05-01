<template>
		<thead>
			<tr>
				<th></th>
				<th v-for="keuze in keuzes">{{keuze.description}}</th>
			</tr>
		</thead>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import {KeuzeOptie} from '../../model/groep';

	@Component({})
	export default class GroepHeaderRow extends Vue {
		@Prop()
		protected keuzes: KeuzeOptie[];
	}
</script>

<style scoped>

</style>


