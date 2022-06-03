<template>
	<div class="mb-3">
		<div class="custom-control custom-checkbox">
			<input
				:id="`customCheck${keuze.naam}`"
				type="checkbox"
				class="custom-control-input"
				:checked="value"
				@input="$emit('input', $event.target.checked)"
			/>
			<label class="custom-control-label" :for="`customCheck${keuze.naam}`">{{ keuze.description }}</label>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import { KeuzeOptie } from '../../../model/groep';

@Component({})
export default class CheckboxKeuze extends Vue {
	@Prop()
	keuze: KeuzeOptie;

	@Prop()
	value: boolean;
}
</script>

<style scoped></style>
