<template>
	<div class="card peiling">
		<div class="card-body">
			<a v-if="isMod" :href="beheerUrl" class="bewerken">
				<Icon icon="pencil" />
			</a>
			<span class="totaal">{{ strAantalGestemd }}</span>
			<h3 class="card-title">
				{{ titel }}
			</h3>
			<!-- eslint-disable-next-line vue/no-v-html vue/max-attributes-per-line -->
			<p class="card-text pt-2" v-html="beschrijving" />
		</div>
		<div>
			<div v-if="heeftGestemd && !resultaatZichtbaar">
				<div class="card-body">Bedankt voor het stemmen!</div>
			</div>
			<div v-else class="card-body">
				<div v-if="zoekbalkZichtbaar" class="pb-2">
					<input v-model="zoekterm" type="text" placeholder="Zoeken" class="form-control" />
				</div>
				<ul class="list-group list-group-flush">
					<li v-for="optie in optiesZichtbaar" :key="optie.id" class="list-group-item">
						<PeilingOptie
							:id="optie.id"
							:key="optie.id"
							v-model="optie.selected"
							:peiling-id="id"
							:titel="optie.titel"
							:beschrijving="optie.beschrijving_formatted"
							:stemmen="optie.stemmen"
							:mag-stemmen="magStemmen"
							:heeft-gestemd="heeftGestemd"
							:aantal-gestemd="aantalGestemd"
							:keuzes-over="keuzesOver"
							:selected="optie.selected"
						/>
					</li>
				</ul>
				<b-pagination
					v-if="optiesFiltered.length > paginaSize"
					v-model="huidigePagina"
					size="md"
					align="center"
					:limit="15"
					:total-rows="optiesFiltered.length"
					:per-page="paginaSize"
				/>
			</div>
		</div>

		<div v-if="!heeftGestemd && magStemmen" class="card-footer footer">
			<div>{{ strKeuzes }}</div>
			<PeilingOptieToevoegen v-if="aantalVoorstellen > 0" :id="id" />

			<input type="button" class="btn btn-primary" value="Stem" :disabled="selected.length === 0" @click="stem" />
		</div>
	</div>
</template>

<script lang="ts">
import $ from 'jquery';
import axios from 'axios';
import Vue from 'vue';
import { Component, Prop } from 'vue-property-decorator';
import Icon from '../common/Icon.vue';
import PeilingOptie from './PeilingOptie.vue';
import PeilingOptieToevoegen from './PeilingOptieToevoegen.vue';

interface PeilingSettings {
	id: string;
	titel: string;
	beschrijving: string;
	resultaat_zichtbaar: boolean;
	aantal_voorstellen: number;
	aantal_stemmen: number;
	aantal_gestemd: number;
	is_mod: boolean;
	mag_stemmen: boolean;
	heeft_gestemd: boolean;
	opties: PeilingOptieSettings[];
}

interface PeilingOptieSettings {
	id: string;
	titel: string;
	beschrijving_formatted: string;
	selected: boolean;
	stemmen: number;
}

@Component({
	components: { Icon, PeilingOptie, PeilingOptieToevoegen },
})
export default class Peiling extends Vue {
	@Prop()
	settings: PeilingSettings;

	id = '';
	titel = '';
	beschrijving = '';
	resultaatZichtbaar = false;
	aantalVoorstellen = 0;
	aantalStemmen = 0;
	aantalGestemd = 0;
	isMod = false;
	heeftGestemd = false;
	magStemmen = false;
	opties: PeilingOptieSettings[] = [];
	zoekterm = '';
	huidigePagina = 1;
	paginaSize = 10;

	private created() {
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
		document.addEventListener('modalClose', () => this.reload());
	}

	private get beheerUrl() {
		return `/peilingen/beheer/${this.id}`;
	}

	private get selected() {
		return this.opties.filter((o) => o.selected);
	}

	private get optiesFiltered() {
		return this.opties.filter((o) => o.titel.toLowerCase().includes(this.zoekterm.toLowerCase()));
	}

	private get optiesZichtbaar() {
		const begin = (this.huidigePagina - 1) * this.paginaSize;
		const eind = begin + this.paginaSize;

		return this.optiesFiltered.slice(begin, eind);
	}

	private get keuzesOver() {
		return this.aantalStemmen - this.selected.length > 0;
	}

	private get strKeuzes() {
		return `${this.selected.length} van de ${this.aantalStemmen} geselecteerd`;
	}

	private get strAantalGestemd() {
		return this.aantalGestemd > 0 ? `(${this.aantalGestemd} stem${this.aantalGestemd > 1 ? 'men' : ''})` : '';
	}

	private get zoekbalkZichtbaar() {
		return this.opties.length > 10;
	}

	private stem() {
		axios
			.post(`/peilingen/stem/${this.id}`, {
				opties: this.selected.map((o) => o.id),
			})
			.then(() => {
				this.heeftGestemd = true;
				this.aantalGestemd = this.aantalGestemd + this.selected.length;
				this.reload();
			});
	}

	private reload() {
		axios.post(`/peilingen/opties/${this.id}`).then((response) => {
			this.opties = response.data.data;
		});
	}
}
</script>

<style scoped>
.bewerken,
.totaal {
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
