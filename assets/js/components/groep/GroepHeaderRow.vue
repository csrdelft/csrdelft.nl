<template>
	<thead>
		<tr>
			<th scope="col" />
			<th scope="col" v-for="keuze in keuzes" :key="keuze.description">
				{{ keuze.description }}
			</th>
		</tr>
	</thead>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { KeuzeOptie } from '../../model/groep';

@Component({})
export default class GroepHeaderRow extends Vue {
	@Prop()
	keuzes: KeuzeOptie[];
}
</script>

<style scoped></style>
