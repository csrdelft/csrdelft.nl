<template>
	<div class="card peiling">
		<div class="card-body">
			<a :href="beheerUrl" v-if="isMod" class="bewerken">
				<Icon icon="pencil"/>
			</a>
			<span class="totaal">{{strAantalGestemd}}</span>
			<h3 class="card-title">{{titel}}</h3>
			<p class="card-text pt-2" v-html="beschrijving"></p>
		</div>
		<div>
			<div v-if="heeftGestemd && !resultaatZichtbaar">
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
							:magStemmen="magStemmen"
							:heeftGestemd="heeftGestemd"
							:aantalGestemd="aantalGestemd"
							:keuzesOver="keuzesOver"
							:selected="optie.selected"></PeilingOptie>
					</li>
				</ul>
				<b-pagination
					v-if="optiesFiltered.length > paginaSize"
					size="md"
					align="center"
					v-model="huidigePagina"
					:limit="15"
					:total-rows="optiesFiltered.length"
					:per-page="paginaSize">
				</b-pagination>
			</div>
		</div>

		<div v-if="!heeftGestemd && magStemmen" class="card-footer footer">
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
			settings: {}
		},
		data: () => ({
			id: null,
			titel: null,
			beschrijving: null,
			resultaatZichtbaar: null,
			aantalVoorstellen: null,
			aantalStemmen: null,
			aantalGestemd: null,
			isMod: null,
			heeftGestemd: null,
			magStemmen: null,
			opties: null,
			alleOpties: [],
			zoekterm: '',
			huidigePagina: 1,
			paginaSize: 10,
		}),
		created() {
			this.id = this.settings.id;
			this.titel = this.settings.titel;
			this.beschrijving = this.settings.beschrijving;
			this.resultaatZichtbaar = this.settings.resultaat_zichtbaar;
			this.aantalVoorstellen = this.settings.aantal_voorstellen;
			this.aantalStemmen = this.settings.aantal_stemmen;
			this.aantalGestemd = this.settings.aantal_gestemd;
			this.isMod = this.settings.is_mod;
			this.heeftGestemd = this.settings.heeft_gestemd;
			this.magStemmen = this.settings.mag_stemmen;
			this.opties = this.settings.opties;

			// Als er op deze pagina een modal gesloten wordt is dat misschien die van
			// de optie toevoegen modal. Dit is de enige manier om dit te weten op dit moment
			$(document.body).on('modalClose', () => this.reload());
		},
		computed: {
			beheerUrl() {
				return `/peilingen/beheer/${this.id}`;
			},
			selected() {
				return this.opties.filter((o) => o.selected);
			},
			optiesFiltered() {
				return this.opties.filter((o) => o.titel.toLowerCase().includes(this.zoekterm.toLowerCase()));
			},
			optiesZichtbaar() {
				let begin = (this.huidigePagina - 1) * this.paginaSize;
				let eind = begin + this.paginaSize;

				return this.optiesFiltered.slice(begin, eind);
			},
			keuzesOver() {
				return this.aantalStemmen - this.selected.length > 0;
			},
			strKeuzes() {
				return `${this.selected.length} van de ${this.aantalStemmen} geselecteerd`;
			},
			strAantalGestemd() {
				return this.aantalGestemd > 0 ? `(${this.aantalGestemd} stem${this.aantalGestemd> 1 ? 'men' : ''})` : '';
			},
			zoekbalkZichtbaar() {
				return this.opties.length > 10;
			}
		},
		methods: {
			stem() {
				axios
					.post(`/peilingen/stem/${this.id}`, {
						opties: this.selected.map((o) => o.id)
					})
					.then(() => {
						this.heeftGestemd = true;
						this.aantalGestemd = this.aantalGestemd + this.selected.length;
						this.reload();
					});
			},
			reload() {
				axios
					.post(`/peilingen/opties/${this.id}`)
					.then((response) => {
						this.opties = response.data.data;
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

	.pagination {
		margin-top: 1.25rem;
		margin-bottom: 0;
	}
</style>
