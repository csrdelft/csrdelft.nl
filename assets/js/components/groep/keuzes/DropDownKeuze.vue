<template>
	<div class="mb-3">
		<label class="w-100"
			>{{ keuze.description }}
			<select class="form-select" :name="'DropDownKeuze-' + keuze.naam" @input="$emit('input', $event.target.value)">
				<option v-for="(optie, i) in keuze.opties" :key="i" :value="optie" :selected="optie === value">
					{{ optie }}
				</option>
			</select>
		</label>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { KeuzeOptie } from '../../../model/groep';

@Component({})
export default class DropDownKeuze extends Vue {
	@Prop()
	keuze: KeuzeOptie;

	@Prop()
	value: string;
}
</script>

<style scoped></style>
