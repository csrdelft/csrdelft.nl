<template>
	<div class="card peiling">
		<a :href="beheerUrl" v-if="is_mod" class="bewerken"><span class="ico pencil"></span></a>
		<div class="card-body">
			<span class="totaal">{{aantal_stemmen_str}}</span>
			<h3 class="card-title">{{titel}}</h3>
			<p class="card-text">{{beschrijving}}</p>
		</div>

		<ul class="list-group list-group-flush"
				v-for="(optie, index) in opties_nieuw"
				:item="optie">
			<li class="list-group-item">
				<PeilingOptie
					v-model="selectedOpties[index]"
					:id="optie.id"
					:peiling_id="optie.peiling_id"
					:titel="optie.titel"
					:beschrijving="optie.beschrijving"
					:stemmen="optie.stemmen"
					:ingebracht_door="optie.ingebracht_door"></PeilingOptie>
			</li>
		</ul>

		<div class="card-body">
			<div v-if="!heeft_gestemd">{{keuzes}}</div>
			<PeilingOptieToevoegen v-if="aantal_voorstellen > 0 && !heeft_gestemd"></PeilingOptieToevoegen>

			<input
				type="button"
				class="btn btn-primary"
				value="Stem"
				:disabled="selected.length === 0"
				v-on:click="stem"
				v-if="!heeft_gestemd"/>
		</div>
	</div>
</template>

<script>
	import axios from 'axios';
	import PeilingOptieToevoegen from "./PeilingOptieToevoegen";
	import PeilingOptie from "./PeilingOptie";

	export default {
		name: 'Peiling',
		components: {PeilingOptie, PeilingOptieToevoegen},
		props: {
			id: Number,
			titel: String,
			beschrijving: String,
			resultaat_zichtbaar: Boolean,
			aantal_voorstellen: Number,
			aantal_keuzes: Number,
			aantal_stemmen: Number,
			rechten_stemmen: String,
			is_mod: Boolean,
			heeft_gestemd: Boolean,
			opties: {
				type: Array,
				default: () => []
			}
		},
		data: () => ({
			opties_nieuw: [],
			selectedOpties: []
		}),
		created() {
			this.opties_nieuw = this.opties;
			$(document.body).on('modalClose', (event) => {
				this.reload();
				console.log('misschien is de peiling veranderd!')
			})
		},
		computed: {
			aantal_stemmen_str() {
				return this.aantal_stemmen > 0 ? `(${this.aantal_stemmen} stem${this.aantal_stemmen > 1 ? 'men' : ''})` : '';
			},
			beheerUrl() {
				return `/peilinge/beheer/${this.id}`;
			},
			selected() {
				return this.selectedOpties.filter(o => o !== null && o.checked);
			},
			keuzesOver() {
				return this.aantal_keuzes - this.selected.length > 0;
			},
			keuzes() {
				return `${this.selected.length} van de ${this.aantal_keuzes} geselecteerd`;
			}
		},
		methods: {
			stem() {
				axios.post(`/peilingen/stem/${this.id}`, {
					opties: this.selected
				})
					.then((response) => {
						this.heeft_gestemd = true; // To data
					})
			},
			reload() {
				axios.post(`/peilingen/opties/${this.id}`)
					.then((response) => {
						this.opties_nieuw = response.data.data;
					})
			}
		}
	}
</script>

<style scoped>
	.peiling {

	}

	.bewerken, .totaal {
		float: right;
	}
</style>
