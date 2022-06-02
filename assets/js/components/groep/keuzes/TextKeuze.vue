<template>
	<div class="mb-3">
		<label :for="'customCheck' + keuze.naam">{{ keuze.description }}</label>
		<input
			:id="'customCheck' + keuze.naam"
			type="text"
			class="form-control"
			:value="value"
			@input="$emit('input', $event.target.value)"
		/>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { KeuzeOptie } from '../../../model/groep';

@Component({})
export default class TextKeuze extends Vue {
	@Prop()
	keuze: KeuzeOptie;

	@Prop()
	value: string;
}
</script>

<style scoped></style>
