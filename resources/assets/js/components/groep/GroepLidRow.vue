<template>
	<tr>
		<td class="text-nowrap" v-html="lid.link"></td>
		<td v-for="keuze in keuzes" v-html="renderSelectie(keuze)"></td>
	</tr>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import GroepKeuzeType from '../../enum/GroepKeuzeType';
	import {GroepLid, KeuzeOptie} from '../../model/groep';
	import {htmlEncode} from '../../util';

	@Component({})
	export default class GroepLidRow extends Vue {
		@Prop()
		private lid: GroepLid;

		@Prop()
		private keuzes: KeuzeOptie[];

		private renderSelectie(keuze: KeuzeOptie) {
			const lidKeuze = this.lid.opmerking2.find((k) => k.naam === keuze.naam);

			if (lidKeuze === undefined) {
				return '<span class="ico bullet_error"></span>';
			}

			switch (keuze.type) {
				case GroepKeuzeType.CHECKBOX:
					return lidKeuze.selectie ? '<span class="ico tick"></span>' : '<span class="ico cross"></span>';
				default:
					return htmlEncode(lidKeuze.selectie);
			}
		}
	}
</script>

<style scoped>

</style>

