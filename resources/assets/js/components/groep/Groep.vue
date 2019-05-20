<template>
	<div class="card groep">
		<div class="card-body">
			<h3 class="card-title">{{naam}}</h3>
			<div class="row">
				<div class="left-col col-md-5" v-if="!aangemeld">
					<p class="card-text">{{samenvatting}}</p>
					<GroepAanmeldForm :keuzes="keuzelijst2" :opmerking="mijnOpmerking" :aangemeld="aangemeld" @aanmelden="aanmelden"/>
				</div>
				<div class="col results">
					<table class="table table-sm">
						<GroepHeaderRow :keuzes="keuzelijst2"/>
						<tbody>
						<GroepLidRow v-for="lid of leden" :key="lid.uid" :lid="lid" :keuzes="keuzelijst2"/>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
	import axios from 'axios';
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import {GroepInstance, GroepKeuzeSelectie, GroepLid, GroepSettings, KeuzeOptie} from '../../model/groep';
	import GroepAanmeldForm from './GroepAanmeldForm.vue';
	import GroepHeaderRow from './GroepHeaderRow.vue';
	import GroepLidRow from './GroepLidRow.vue';

	// noinspection JSUnusedGlobalSymbols
	@Component({components: {GroepAanmeldForm, GroepLidRow, GroepHeaderRow}})
	export default class Groep extends Vue {
		/// Props
		@Prop()
		private settings: GroepSettings;
		@Prop()
		private groep: GroepInstance;

		/// Data
		private id: number = 0;
		private naam: string = '';
		private familie: string = '';
		private beginMoment: Date = new Date();
		private eindMoment: Date = new Date();
		private status: string = '';
		private samenvatting: string = '';
		private omschrijving: string = '';
		private makerUid: string = '';
		private versie: string = '';
		private keuzelijst2: KeuzeOptie[] = [];
		private leden: GroepLid[] = [];
		private mijnUid: string = '';
		private mijnLink: string = '';
		private aanmeldUrl: string = '';
		private mijnOpmerking: GroepKeuzeSelectie[] = [];

		protected created() {
			this.id = this.groep.id;
			this.naam = this.groep.naam;
			this.familie = this.groep.familie;
			this.beginMoment = this.groep.begin_moment;
			this.eindMoment = this.groep.eind_moment;
			this.status = this.groep.status;
			this.samenvatting = this.groep.samenvatting;
			this.omschrijving = this.groep.omschrijving;
			this.makerUid = this.groep.maker_uid;
			this.versie = this.groep.versie;
			this.keuzelijst2 = this.groep.keuzelijst2;
			this.leden = this.groep.leden;

			this.mijnUid = this.settings.mijn_uid;
			this.mijnLink = this.settings.mijn_link;
			this.aanmeldUrl = this.settings.aanmeld_url;

			if (this.aangemeld) {
				this.mijnOpmerking = this.mijnAanmelding!.opmerking2;
			} else {
				this.mijnOpmerking = this.keuzelijst2.map((value) => ({
					selectie: value.default,
					naam: value.naam,
				}));
			}
		}

		/// Getters
		private get mijnAanmelding() {
			return this.leden.find((lid) => lid.uid === this.mijnUid);
		}

		protected get aangemeld() {
			return this.mijnAanmelding !== undefined;
		}

		/// Methods
		protected aanmelden(event: Event) {
			if (!this.aangemeld) {
				this.leden.push({
					uid: this.mijnUid,
					link: this.mijnLink,
					opmerking2: this.mijnOpmerking,
				});

				axios.post(this.aanmeldUrl, {opmerking2: this.mijnOpmerking});
			}
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
		overflow: auto;
	}
</style>
