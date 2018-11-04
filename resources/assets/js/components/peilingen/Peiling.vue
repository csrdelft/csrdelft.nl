<template>
	<div class="card peiling">
		<a :href="beheerUrl" v-if="isMod" class="bewerken"><span class="ico pencil"></span></a>
		<div class="card-body">
			<span class="totaal">{{strAantalStemmen}}</span>
			<h3 class="card-title">{{titel}}</h3>
			<p class="card-text">{{beschrijving}}</p>
		</div>

		<ul class="list-group list-group-flush"
				v-for="(optie, index) in alleOpties"
				:item="optie">
			<li class="list-group-item">
				<PeilingOptie
					v-model="selectedOpties[index]"
					:id="optie.id"
					:peilingId="optie.peiling_id"
					:titel="optie.titel"
					:beschrijving="optie.beschrijving"
					:stemmen="optie.stemmen"
					:ingebrachtDoor="optie.ingebracht_door"></PeilingOptie>
			</li>
		</ul>

		<div class="card-body">
			<div v-if="!heeftGestemd">{{strKeuzes}}</div>
			<PeilingOptieToevoegen v-if="aantalVoorstellen > 0 && !heeftGestemd"></PeilingOptieToevoegen>

			<input
				type="button"
				class="btn btn-primary"
				value="Stem"
				:disabled="selected.length === 0"
				v-on:click="stem"
				v-if="!heeftGestemd"/>
		</div>
	</div>
</template>

<script>
	import axios from 'axios';
	import PeilingOptieToevoegen from './PeilingOptieToevoegen';
	import PeilingOptie from './PeilingOptie';

	export default {
		name: 'Peiling',
		components: {PeilingOptie, PeilingOptieToevoegen},
		props: {
			id: Number,
			titel: String,
			beschrijving: String,
			resultaatZichtbaar: Boolean,
			aantalVoorstellen: Number,
			aantalKeuzes: Number,
			aantalStemmen: Number,
			rechtenStemmen: String,
			isMod: Boolean,
			heeftGestemd: Boolean,
			opties: {
				type: Array,
				default: () => []
			}
		},
		data: () => ({
			alleOpties: [],
			selectedOpties: [],
		}),
		created() {
			// Sla opties op in een data attribuut, deze wordt niet van boven veranderd,
			// maar wel wanneer er een request wordt gedaan.
			this.alleOpties = this.opties;

			// Als er op deze pagina een modal gesloten wordt is dat misschien die van
			// de optie toevoegen modal. Dit is de enige manier om dit te weten op dit moment
			$(document.body).on('modalClose', () => {
				this.reload();
			});
		},
		computed: {
			beheerUrl() {
				return `/peilinge/beheer/${this.id}`;
			},
			selected() {
				return this.selectedOpties.filter((o) => o !== null && o.checked);
			},
			keuzesOver() {
				return this.aantalKeuzes - this.selected.length > 0;
			},
			strKeuzes() {
				return `${this.selected.length} van de ${this.aantalKeuzes} geselecteerd`;
			},
			strAantalStemmen() {
				return this.aantalStemmen > 0 ? `(${this.aantalStemmen} stem${this.aantalStemmen > 1 ? 'men' : ''})` : '';
			},
		},
		methods: {
			stem() {
				axios
					.post(`/peilingen/stem/${this.id}`, {
						opties: this.selected
					})
					.then(() => {
						this.heeftGestemd = true; // To data
					});
			},
			reload() {
				axios
					.post(`/peilingen/opties/${this.id}`)
					.then((response) => {
						this.alleOpties = response.data.data;
					});
			}
		}
	};
</script>

<style scoped>
	.peiling {

	}

	.bewerken, .totaal {
		float: right;
	}
</style>
