<template>
  <div class="card peiling">
    <div class="card-body">
      <a v-if="data.isMod" :href="beheerUrl" class="bewerken">
        <Icon icon="pencil" />
      </a>
      <span class="totaal">{{ strAantalGestemd }}</span>
      <h3 class="card-title">
        {{ data.titel }}
      </h3>
      <!-- eslint-disable-next-line vue/no-v-html vue/max-attributes-per-line -->
      <p class="card-text pt-2" v-html="data.beschrijving" />
    </div>
    <div>
      <div v-if="data.heeftGestemd && !data.resultaatZichtbaar">
        <div class="card-body">Bedankt voor het stemmen!</div>
      </div>
      <div v-else class="card-body">
        <div v-if="zoekbalkZichtbaar" class="pb-2">
          <input
            v-model="data.zoekterm"
            type="text"
            placeholder="Zoeken"
            class="form-control"
          />
        </div>
        <ul class="list-group list-group-flush">
          <li
            v-for="optie in optiesZichtbaar"
            :key="optie.id"
            class="list-group-item"
          >
            <PeilingOptie
              :id="optie.id"
              :key="optie.id"
              v-model="optie.selected"
              :titel="optie.titel"
              :beschrijving="optie.beschrijving_formatted"
              :stemmen="optie.stemmen"
              :mag-stemmen="data.magStemmen"
              :heeft-gestemd="data.heeftGestemd"
              :aantal-gestemd="data.aantalGestemd"
              :keuzes-over="keuzesOver"
            />
          </li>
        </ul>
        <paginate
          v-if="optiesFiltered.length > data.paginaSize"
          v-model="data.huidigePagina"
          :page-count="Math.ceil(optiesFiltered.length / data.paginaSize)"
          :prev-text="'Vorige'"
          :next-text="'Volgende'"
          :click-handler="zetHuidigePagina"
          size="md"
          align="center"
          :limit="15"
          :total-rows="optiesFiltered.length"
          :per-page="data.paginaSize"
        />
      </div>
    </div>

    <div
      v-if="!data.heeftGestemd && data.magStemmen"
      class="card-footer footer"
    >
      <div>{{ strKeuzes }}</div>
      <PeilingOptieToevoegen v-if="data.aantalVoorstellen > 0" :id="data.id" />

      <input
        type="button"
        class="btn btn-primary"
        value="Stem"
        :disabled="selected.length === 0"
        @click="stem"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import Paginate from 'vuejs-paginate-next';
import { computed, onMounted, reactive } from 'vue';
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

const props = defineProps<{ settings: PeilingSettings }>();

const data = reactive({
  id: '',
  titel: '',
  beschrijving: '',
  resultaatZichtbaar: false,
  aantalVoorstellen: 0,
  aantalStemmen: 0,
  aantalGestemd: 0,
  isMod: false,
  heeftGestemd: false,
  magStemmen: false,
  opties: [] as PeilingOptieSettings[],
  zoekterm: '',
  huidigePagina: 1,
  paginaSize: 10,
});

const beheerUrl = computed(() => `/peilingen/beheer/${data.id}`);
const selected = computed(() => data.opties.filter((o) => o.selected));
const optiesFiltered = computed(() =>
  data.opties.filter((o) =>
    o.titel.toLowerCase().includes(data.zoekterm.toLowerCase())
  )
);
const optiesZichtbaar = computed(() => {
  const begin = (data.huidigePagina - 1) * data.paginaSize;
  const eind = begin + data.paginaSize;

  return optiesFiltered.value.slice(begin, eind);
});
const keuzesOver = computed(
  () => data.aantalStemmen - selected.value.length > 0
);
const strKeuzes = computed(
  () => `${selected.value.length} van de ${data.aantalStemmen} geselecteerd`
);
const strAantalGestemd = computed(() =>
  data.aantalGestemd > 0
    ? `(${data.aantalGestemd} stem${data.aantalGestemd > 1 ? 'men' : ''})`
    : ''
);
const zoekbalkZichtbaar = computed(() => data.opties.length > 10);

const stem = () =>
  axios
    .post(`/peilingen/stem/${data.id}`, {
      opties: selected.value.map((o) => o.id),
    })
    .then(() => {
      data.heeftGestemd = true;
      data.aantalGestemd = data.aantalGestemd + selected.value.length;
      reload();
    });
const reload = () =>
  axios.post(`/peilingen/opties/${data.id}`).then((response) => {
    data.opties = response.data.data;
  });
const zetHuidigePagina = (paginaNum) => (data.huidigePagina = paginaNum);

onMounted(() => {
  data.id = props.settings.id;
  data.titel = props.settings.titel;
  data.beschrijving = props.settings.beschrijving;
  data.resultaatZichtbaar = props.settings.resultaat_zichtbaar;
  data.aantalVoorstellen = props.settings.aantal_voorstellen;
  data.aantalStemmen = props.settings.aantal_stemmen;
  data.aantalGestemd = props.settings.aantal_gestemd;
  data.isMod = props.settings.is_mod;
  data.heeftGestemd = props.settings.heeft_gestemd;
  data.magStemmen = props.settings.mag_stemmen;
  data.opties = props.settings.opties;

  // Als er op deze pagina een modal gesloten wordt is dat misschien die van
  // de optie toevoegen modal. Dit is de enige manier om dit te weten op dit moment
  document.addEventListener('modalClose', () => reload());
});
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
