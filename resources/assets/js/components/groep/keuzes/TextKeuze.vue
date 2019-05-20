<template>
	<div class="form-group">
		<label :for="'customCheck' + keuze.naam">{{keuze.description}}</label>
		<input type="text" class="form-control" :id="'customCheck' + keuze.naam" :value="value" @input="$emit('input', $event.target.value)">
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import {GroepKeuzeSelectie, KeuzeOptie} from '../../../model/groep';

	@Component({})
	export default class TextKeuze extends Vue {
		@Prop()
		private keuze: KeuzeOptie;

		@Prop()
		private value: string;
	}
</script>

<style scoped>

</style>

