<template>
	<div class="card peiling">
		<div class="card-body">
			<a :href="beheerUrl" v-if="isMod" class="bewerken">
				<Icon icon="pencil"/>
			</a>
			<span class="totaal">{{strAantalStemmen}}</span>
			<h3 class="card-title">{{titel}}</h3>
			<p class="card-text pt-2" v-html="beschrijving"></p>
		</div>
		<div>
			<div v-if="dataHeeftGestemd && !resultaatZichtbaar">
				<div class="card-body">Bedankt voor het stemmen!</div>
			</div>
			<div v-else class="card-body">
				<div v-if="zoekbalkZichtbaar" class="pb-2">
					<input type="text" placeholder="Zoeken" v-model="zoekterm" class="form-control"/>
				</div>
				<ul class="list-group list-group-flush">
					<li class="list-group-item" v-for="optie in optiesZichtbaar">
						<PeilingOptie
							v-model="optie.selected"
							:key="optie.id"
							:id="optie.id"
							:peilingId="optie.peiling_id"
							:titel="optie.titel"
							:beschrijving="optie.beschrijving"
							:stemmen="optie.stemmen"
							:selected="optie.selected"
							:ingebrachtDoor="optie.ingebracht_door"></PeilingOptie>
					</li>
				</ul>
				<b-pagination
					size="md"
					align="center"
					v-model="huidigePagina"
					:limit="15"
					:total-rows="optiesFiltered.length"
					:per-page="paginaSize">
				</b-pagination>
			</div>
		</div>

		<div v-if="!dataHeeftGestemd" class="card-footer footer">
			<div>{{strKeuzes}}</div>
			<PeilingOptieToevoegen v-if="aantalVoorstellen > 0"></PeilingOptieToevoegen>

			<input
				type="button"
				class="btn btn-primary"
				value="Stem"
				:disabled="selected.length === 0"
				v-on:click="stem"/>
		</div>
	</div>
</template>

<script>
	import axios from 'axios';
	import PeilingOptieToevoegen from './PeilingOptieToevoegen';
	import PeilingOptie from './PeilingOptie';
	import Icon from '../common/Icon';

	export default {
		name: 'Peiling',
		components: {Icon, PeilingOptie, PeilingOptieToevoegen},
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
			dataHeeftGestemd: false,
			dataAantalStemmen: 0,
			zoekterm: '',
			huidigePagina: 1,
			paginaSize: 5,
		}),
		created() {
			// Sla opties op in een data attribuut, deze wordt niet van boven veranderd,
			// maar wel wanneer er een request wordt gedaan.
			this.alleOpties = this.opties;
			this.dataHeeftGestemd = this.heeftGestemd;
			this.dataAantalStemmen = this.aantalStemmen;

			// Als er op deze pagina een modal gesloten wordt is dat misschien die van
			// de optie toevoegen modal. Dit is de enige manier om dit te weten op dit moment
			$(document.body).on('modalClose', () => {
				this.reload();
			});
		},
		computed: {
			beheerUrl() {
				return `/peilingen/beheer/${this.id}`;
			},
			selected() {
				return this.alleOpties.filter((o) => o.selected);
			},
			optiesFiltered() {
				return this.alleOpties.filter((o) => o.titel.toLowerCase().includes(this.zoekterm.toLowerCase()));
			},
			optiesZichtbaar() {
				let begin = (this.huidigePagina - 1) * this.paginaSize;
				let eind = begin + this.paginaSize;

				return this.optiesFiltered.slice(begin, eind);
			},
			keuzesOver() {
				return this.aantalKeuzes - this.selected.length > 0;
			},
			strKeuzes() {
				return `${this.selected.length} van de ${this.aantalKeuzes} geselecteerd`;
			},
			strAantalStemmen() {
				return this.dataAantalStemmen > 0 ? `(${this.dataAantalStemmen} stem${this.dataAantalStemmen > 1 ? 'men' : ''})` : '';
			},
			zoekbalkZichtbaar() {
				return this.alleOpties.length > 10;
			}
		},
		methods: {
			stem() {
				axios
					.post(`/peilingen/stem/${this.id}`, {
						opties: this.selected.map((o) => o.id)
					})
					.then(() => {
						this.dataHeeftGestemd = true; // To data
						this.dataAantalStemmen = this.dataAantalStemmen + this.selected.length;
						this.reload();
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
	.bewerken, .totaal {
		float: right;
	}

	.footer {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
	}
</style>
