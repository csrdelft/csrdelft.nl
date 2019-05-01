<template>
	<div class="card groep">
		<div class="card-body">
			<div class="row">
				<div class="col"><h5 class="card-title">{{naam}}</h5></div>
			</div>
			<div class="row">
				<div class="left-col col-md-4" v-if="!aangemeld">
					<p class="card-text">{{samenvatting}}</p>
					<div v-for="(keuze, i) in keuzelijst2">
						<CheckboxKeuze v-if="keuze.type === GroepKeuzeType.CHECKBOX" :key="i" :keuze="keuze"
													 v-model="mijn_opmerking[i]"/>
					</div>
					<a class="btn btn-primary" href="#" @click="aanmelden">Aanmelden</a>
				</div>
				<div class="col results">
					<table class="table table-sm">
						<GroepHeaderRow :keuzes="keuzelijst2"/>
						<tbody>
						<GroepLidRow v-for="lid of leden" :lid="lid" :keuzes="keuzelijst2"/>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
	import axios from "axios";
	import Vue from "vue";
	import {Component, Prop} from "vue-property-decorator";
	import GroepKeuzeType from "../../enum/GroepKeuzeType";
	import {GroepInstance, GroepKeuzeSelectie, GroepLid, GroepSettings, KeuzeOptie} from "../../model/groep";
	import GroepHeaderRow from "./GroepHeaderRow.vue";
	import GroepLidRow from "./GroepLidRow.vue";
	import CheckboxKeuze from "./keuzes/CheckboxesKeuzes.vue";

	// noinspection JSUnusedGlobalSymbols
	@Component({components: {CheckboxKeuze, GroepLidRow, GroepHeaderRow}})
	export default class Groep extends Vue {
		/// Props
		@Prop()
		private settings: GroepSettings;
		@Prop()
		private groep: GroepInstance;

		/// Data
		private id: number = 0;
		private naam: string = "";
		private familie: string = "";
		private begin_moment: Date = new Date();
		private eind_moment: Date = new Date();
		private status: string = "";
		private samenvatting: string = "";
		private omschrijving: string = "";
		private maker_uid: string = "";
		private versie: string = "";
		private keuzelijst2: KeuzeOptie[] = [];
		private leden: GroepLid[] = [];
		private mijn_uid: string = "";
		private mijn_link: string = "";
		private aanmeld_url: string = "";
		private mijn_opmerking: GroepKeuzeSelectie[] = [];

		GroepKeuzeType = GroepKeuzeType;

		protected created() {
			this.id = this.groep.id;
			this.naam = this.groep.naam;
			this.familie = this.groep.familie;
			this.begin_moment = this.groep.begin_moment;
			this.eind_moment = this.groep.eind_moment;
			this.status = this.groep.status;
			this.samenvatting = this.groep.samenvatting;
			this.omschrijving = this.groep.omschrijving;
			this.maker_uid = this.groep.maker_uid;
			this.versie = this.groep.versie;
			this.keuzelijst2 = this.groep.keuzelijst2;
			this.leden = this.groep.leden;

			this.mijn_uid = this.settings.mijn_uid;
			this.mijn_link = this.settings.mijn_link;
			this.aanmeld_url = this.settings.aanmeld_url;

			if (this.aangemeld) {
				this.mijn_opmerking = this.mijnAanmelding!.opmerking;
			} else {
				this.mijn_opmerking = this.keuzelijst2.map(value => ({
					selectie: value.default,
					naam: value.naam,
					type: value.type
				}));
			}
		}

		/// Getters
		private get mijnAanmelding() {
			return this.leden.find((lid) => lid.uid == this.mijn_uid);
		}

		protected get aangemeld() {
			return this.mijnAanmelding !== undefined;
		}

		/// Methods
		protected aanmelden(event: Event) {
			event.preventDefault();
			if (!this.aangemeld) {
				const aanmelding = <GroepLid> {
					uid: this.mijn_uid,
					link: this.mijn_link,
					opmerking: this.mijn_opmerking,
				};
				this.leden.push(aanmelding);

				axios.post(this.aanmeld_url, aanmelding)
			}
			// TODO: Stuur naar server
			return false;
		}
	}
</script>

<style scoped>
	.left-col {
		border-right: 1px solid rgba(0, 0, 0, 0.125);
	}

	.groep {
		min-height: 300px;
	}

	.results {
		height: 300px;
		overflow: auto;
	}
</style>
