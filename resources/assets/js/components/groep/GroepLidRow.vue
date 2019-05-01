<template>
		<tr>
			<td class="text-nowrap" v-html="lid.link"></td>
			<td v-for="keuze in keuzes" v-html="renderSelectie(keuze)"></td>
		</tr>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import {GroepLid, KeuzeOptie} from '../../model/groep';

	@Component({})
	export default class GroepLidRow extends Vue {
		@Prop()
		private lid: GroepLid;

		@Prop()
		private keuzes: KeuzeOptie[];

		private renderSelectie(keuze: KeuzeOptie) {
			const lidKeuze = this.lid.opmerking2.find((lidKeuze) => lidKeuze.naam === keuze.naam);

			if (lidKeuze == undefined) {
				return '<span class="ico bullet_error"></span>';
			}

			switch (keuze.type) {
				case 'checkbox_1':
					if (lidKeuze.selectie) {
						return '<span class="ico tick"></span>';
					} else {
						return '<span class="ico cross"></span>';
					}
				default:
					return '';
			}

		}
	}
</script>

<style scoped>

</style>

