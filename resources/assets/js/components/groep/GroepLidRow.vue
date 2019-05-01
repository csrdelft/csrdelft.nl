<template>
		<tr>
			<td v-html="lid.link"></td>
			<td v-for="opmerking in lid.opmerking" v-html="renderSelectie(opmerking.type, opmerking.selectie)"></td>
		</tr>
</template>

<script lang="ts">
	import Vue from "vue";
	import {Component, Prop} from "vue-property-decorator";
	import {GroepLid, KeuzeOptie} from "../../groep";

	@Component({})
	export default class GroepLidRow extends Vue {
		@Prop()
		private lid: GroepLid;

		@Prop()
		private keuzes: KeuzeOptie[];

		private renderSelectie(type: string, selectie: any) {
			switch (type) {
				case 'checkbox_1':
					if (selectie) {
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

