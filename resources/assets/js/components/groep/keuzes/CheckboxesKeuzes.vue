<template>
	<div class="form-group">
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input" :id="'customCheck' + keuze.naam" v-model="value.selectie">
			<label class="custom-control-label" :for="'customCheck' + keuze.naam">{{keuze.description}}</label>
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from "vue";
	import {Component, Prop} from "vue-property-decorator";
	import {GroepKeuzeSelectie, KeuzeOptie} from "../../../groep";

	@Component({})
	export default class CheckboxKeuze extends Vue {
		@Prop()
		private keuze: KeuzeOptie;

		@Prop()
		private value: GroepKeuzeSelectie;
	}
</script>

<style scoped>

</style>
