<template>
	<div>
		<component v-for="(keuze, i) in keuzes"
							 :is="getComponent(keuze.type)"
							 v-model="opmerking[i]"
							 :key="i"
							 :keuze="keuze"/>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import GroepKeuzeType from '../../enum/GroepKeuzeType';
	import {GroepKeuzeSelectie, KeuzeOptie} from '../../model/groep';
	import CheckboxKeuze from './keuzes/CheckboxKeuze.vue';
	import TextKeuze from './keuzes/TextKeuze.vue';

	@Component({components: {CheckboxKeuze, TextKeuze}})
	export default class GroepAanmeldForm extends Vue {
		@Prop()
		private keuzes: KeuzeOptie[];

		@Prop()
		private opmerking: GroepKeuzeSelectie[];

		private getComponent(type: string) {
			switch (type) {
				case GroepKeuzeType.CHECKBOX: return CheckboxKeuze;
				case GroepKeuzeType.TEXT: return TextKeuze;
				default: throw Error('kannie');
			}
		}
	}
</script>

<style scoped>

</style>
