<template>
  <div>
    <div v-if="data.finished" class="score-blok">
      <div class="titel">
        {{ data.titel }}
      </div>
      <div class="score-titel">Jouw score:</div>
      <div class="score">{{ Math.round(percentageGoed) }}%</div>
    </div>
    <div v-if="!data.started">
      <div class="row">
        <div class="col-sm-6">
          <strong class="mb-1 block">Lichting(en)</strong>
          <div>
            <input
              id="alleLichtingen"
              v-model="data.alleLichtingen"
              type="checkbox"
            />
            <label for="alleLichtingen">Alle lichtingen</label>
          </div>
          <div v-for="lichting in lichtingen" :key="lichting">
            <input
              v-if="!data.alleLichtingen"
              :id="'lichting' + lichting"
              v-model="data.lichtingSelectie"
              type="checkbox"
              :value="lichting"
            />
            <input
              v-if="data.alleLichtingen"
              type="checkbox"
              :checked="data.alleLichtingen"
              disabled
            />
            <label :for="'lichting' + lichting">{{ lichting }}</label>
          </div>
        </div>
        <div class="col-sm-6">
          <strong class="mb-1 block">Verticale(n)</strong>
          <div>
            <input
              id="alleVerticalen"
              v-model="data.alleVerticalen"
              type="checkbox"
            />
            <label for="alleVerticalen">Alle verticalen</label>
          </div>
          <div v-for="verticale in verticalen" :key="verticale">
            <input
              v-if="!data.alleVerticalen"
              :id="'verticale' + verticale"
              v-model="data.verticaleSelectie"
              type="checkbox"
              :value="verticale"
            />
            <input
              v-if="data.alleVerticalen"
              type="checkbox"
              :checked="data.alleVerticalen"
              disabled
            />
            <label :for="'verticale' + verticale">{{ verticale }}</label>
          </div>
        </div>
      </div>
      <div>
        <strong class="mb-1 block">Onderkant verbergen</strong>
        <div>
          <input
            id="verbergOnderkant"
            v-model="data.verbergOnderkant"
            type="checkbox"
          />
          <label for="verbergOnderkant">Voorkom leesbare namen op foto's</label>
        </div>
      </div>
      <strong class="mt-3 mb-1 block">Antwoordmethode</strong>
      <select v-model="data.antwoordMethode" class="form-control">
        <option value="voornaam">Voornaam</option>
        <option value="achternaam">Achternaam</option>
        <option value="combi">Voor- en achternaam</option>
        <option value="civi">Achternaam en achtervoegsel</option>
      </select>
      <a
        href="#"
        class="btn btn-primary btn-block mt-3"
        :class="{ disabled: !klaarVoorDeStart }"
        @click.prevent="start"
      >
        Start met {{ aantal }} {{ aantal === 1 ? 'lid' : 'leden' }}
      </a>
    </div>
    <div v-else-if="!data.finished">
      <div class="progress">
        <div class="correct" :style="{ width: percentageGoed + '%' }" />
        <div class="again" :style="{ width: percentageOpnieuw + '%' }" />
        <div class="wrong" :style="{ width: percentageFout + '%' }" />
      </div>
      <div
        v-if="data.laatste"
        class="laatste"
        :class="{ goed: data.laatsteGoed }"
      >
        <img :src="'/profiel/pasfoto/' + data.laatste.uid + '.jpg'" alt="" />
        <div class="info">
          <div class="naam">
            <span
              :class="{
                bold:
                  data.antwoordMethode === 'voornaam' ||
                  data.antwoordMethode === 'combi',
              }"
            >
              {{ data.laatste.voornaam }}
            </span>
            <span
              :class="{
                bold:
                  data.antwoordMethode === 'achternaam' ||
                  data.antwoordMethode === 'combi' ||
                  data.antwoordMethode === 'civi',
              }"
            >
              {{ data.laatste.tussenvoegsel }} {{ data.laatste.achternaam }}
            </span>
            <span
              v-if="data.laatste.postfix"
              :class="{ bold: data.antwoordMethode === 'civi' }"
            >
              {{ data.laatste.postfix }}
            </span>
          </div>
          <div class="tekst">
            <span>{{ data.laatste.lichting }}</span>
            <span
              v-if="data.laatste.verticale && data.laatste.verticale !== 'Geen'"
            >
              {{ data.laatste.verticale }}
            </span>
            <span>{{ data.laatste.studie }}</span>
          </div>
        </div>
        <Icon v-if="data.laatsteGoed" icon="check" />
        <Icon v-else icon="xmark" />
      </div>
      <div
        class="pasfotoContainer"
        :class="{ onderkantVerbergen: data.verbergOnderkant }"
      >
        <div
          :style="{
            'background-image':
              'url(/profiel/pasfoto/' + data.huidig.uid + '.jpg)',
          }"
          class="pasfoto"
        />
      </div>
      <strong v-if="data.antwoordMethode === 'voornaam'" class="mb-1 block">
        Voornaam:
      </strong>
      <strong v-if="data.antwoordMethode === 'achternaam'" class="mb-1 block">
        Achternaam:
      </strong>
      <strong v-if="data.antwoordMethode === 'combi'" class="mb-1 block">
        Voor- en achternaam:
      </strong>
      <strong v-if="data.antwoordMethode === 'civi'" class="mb-1 block">
        Achternaam en achtervoegsel:
      </strong>
      <input
        type="text"
        class="form-control"
        :value="data.ingevuld"
        autofocus
        @input="data.ingevuld = $event.target.value"
        @keydown.enter="controleer"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive } from 'vue';
import Icon from '../common/Icon.vue';
import { shuffle, uniq } from '../../lib/util';

const preloaded: string[] = [];

const preloadImage = (url: string) => {
  if (preloaded.includes(url)) {
    return;
  }
  preloaded.push(url);
  const img = new Image();
  img.src = url;
};

interface Lid {
  uid: string;
  lichting: string;
  verticale: string;
  tussenvoegsel: string;
  achternaam: string;
  voornaam: string;
  postfix: string;
  studie: string;
}

type AntwoordMethode = 'voornaam' | 'achternaam' | 'civi' | 'combi';

const props = defineProps<{ leden: Lid[] }>();

const data = reactive({
  // Config
  alleLichtingen: false,
  alleVerticalen: true,
  lichtingSelectie: [] as string[],
  verticaleSelectie: [] as string[],
  antwoordMethode: 'voornaam' as AntwoordMethode,
  aantalPerKeer: 5,

  // Game state
  verbergOnderkant: true,
  started: false,
  finished: false,
  goed: [] as Lid[],
  opnieuw: [] as Lid[],
  fout: [] as Lid[],
  todo: [] as Lid[],
  laatste: null as Lid | null,
  laatsteGoed: null as boolean | null,
  huidig: null as Lid | null,
  ingevuld: '',
  titel: '',
});

const aantal = computed(() => gefilterdeLeden.value.length);
const gefilterdeLeden = computed(() =>
  props.leden.filter(
    (lid: Lid) =>
      (data.alleLichtingen || data.lichtingSelectie.includes(lid.lichting)) &&
      (data.alleVerticalen || data.verticaleSelectie.includes(lid.verticale))
  )
);
const lichtingen = computed(() =>
  uniq(props.leden.map((lid) => lid.lichting)).sort()
);
const verticalen = computed(() =>
  uniq(props.leden.map((lid) => lid.verticale)).sort()
);
const klaarVoorDeStart = computed(() => gefilterdeLeden.value.length > 0);
const totaalAantal = computed(
  () =>
    data.todo.length + data.goed.length + data.opnieuw.length + data.fout.length
);
const percentageGoed = computed(() =>
  totaalAantal.value > 0 ? (data.goed.length / totaalAantal.value) * 100 : 0
);
const percentageOpnieuw = computed(() =>
  totaalAantal.value > 0 ? (data.opnieuw.length / totaalAantal.value) * 100 : 0
);
const percentageFout = computed(() =>
  totaalAantal.value > 0 ? (data.fout.length / totaalAantal.value) * 100 : 0
);

const start = () => {
  if (!klaarVoorDeStart.value) {
    return;
  }
  data.started = true;
  data.goed = [];
  data.opnieuw = [];
  data.fout = [];
  data.todo = gefilterdeLeden.value;
  shuffle(data.todo);
  data.huidig = null;
  data.laatste = null;
  data.finished = false;
  volgende();
  data.titel = bouwTitel();
  document.title = `C.S.R. Delft - Namen ${data.titel} leren`;
  window.scrollTo(0, 0);
};
const volgende = () => {
  const choice = data.fout.concat(
    data.todo.slice(0, Math.max(data.aantalPerKeer - data.fout.length, 0))
  );
  const pickable = choice.filter(
    (lid) => choice.length === 1 || !data.huidig || lid.uid !== data.huidig.uid
  );
  if (pickable.length > 0) {
    for (const lid of pickable) {
      preloadImage('/profiel/pasfoto/' + lid.uid + '.jpg');
    }
    data.huidig = pickable[Math.floor(Math.random() * pickable.length)];
    data.ingevuld = '';
  } else {
    data.finished = true;
    data.started = false;
  }
};
const controleer = () => {
  if (data.huidig == null) {
    throw new Error('huidig niet gezet');
  }
  // Antwoord vormen
  const onderdelen = [];
  if (data.antwoordMethode === 'voornaam' || data.antwoordMethode === 'combi') {
    onderdelen.push(data.huidig.voornaam);
  }
  if (
    data.antwoordMethode === 'achternaam' ||
    data.antwoordMethode === 'combi' ||
    data.antwoordMethode === 'civi'
  ) {
    if (data.huidig.tussenvoegsel) {
      onderdelen.push(data.huidig.tussenvoegsel);
    }
    onderdelen.push(data.huidig.achternaam);
  }
  if (data.antwoordMethode === 'civi') {
    if (data.huidig.postfix) {
      onderdelen.push(data.huidig.postfix);
    }
  }
  const antwoord = onderdelen.map((s) => s.trim()).join(' ');

  // Antwoord checken
  data.laatste = data.huidig;
  data.laatsteGoed =
    antwoord.toLowerCase().replace('.', '') ===
    data.ingevuld.toLowerCase().replace('.', '');

  // Verwijderen uit oude lijst en toevoegen aan nieuwe lijst
  let index = data.todo.findIndex((lid) => lid.uid === data.huidig?.uid);
  if (index === -1) {
    // Fout lijst
    if (data.laatsteGoed) {
      index = data.fout.findIndex((lid) => lid.uid === data.huidig?.uid);
      data.fout.splice(index, 1);
      data.opnieuw.push(data.huidig);
    }
  } else {
    // Te doen lijst
    data.todo.splice(index, 1);
    if (data.laatsteGoed) {
      data.goed.push(data.huidig);
    } else {
      data.fout.push(data.huidig);
    }
  }

  volgende();
};
const bouwTitel = () => {
  if (data.alleLichtingen && data.alleVerticalen) {
    return 'Alle leden';
  }

  let titel = '';
  if (!data.alleLichtingen) {
    data.lichtingSelectie.sort();
    titel += 'Lichting ';
    titel += data.lichtingSelectie
      .slice(0, data.lichtingSelectie.length - 1)
      .join(', ');
    if (data.lichtingSelectie.length > 1) {
      titel += ' & ';
    }
    titel += data.lichtingSelectie[data.lichtingSelectie.length - 1];
  }
  if (!data.alleVerticalen) {
    if (titel) {
      titel += ', ';
    }
    data.verticaleSelectie.sort();
    titel += data.verticaleSelectie
      .slice(0, data.verticaleSelectie.length - 1)
      .join(', ');
    if (data.verticaleSelectie.length > 1) {
      titel += ' & ';
    }
    titel += data.verticaleSelectie[data.verticaleSelectie.length - 1];
  }

  return titel;
};
</script>

<style scoped>
.progress {
  height: 20px;
  width: 100%;
  border-radius: 3px;
  background: #adadad;
}

.progress div {
  float: left;
  height: 100%;
  transition: width ease-in-out 0.5s;
}

.progress div.correct {
  background: #2ecc71;
}

.progress div.again {
  background: #f1c40f;
}

.progress div.wrong {
  background: #c0392b;
}

.pasfotoContainer {
  width: 170px;
  height: 255px;
  margin: 15px auto;
  background: url('../../../images/loading-fb.gif') no-repeat center center
    white;
  overflow: hidden;
}

.pasfotoContainer.onderkantVerbergen {
  height: 150px;
}

.pasfotoContainer .pasfoto {
  width: 170px;
  height: 255px;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center center;
}

.laatste {
  border-radius: 6px;
  background: #c0392b;
  color: white;
  overflow: hidden;
  margin-top: 10px;
}

.laatste.goed {
  background: #2ecc71;
}

.laatste .fas {
  display: inline-block;
  font-size: 26px;
  line-height: 90px;
  vertical-align: middle;
  float: right;
  margin: 0 18px 0 15px;
}

.laatste img {
  display: inline-block;
  height: 90px;
}

.laatste .info {
  display: inline-block;
  padding: 29px 0 0 15px;
  vertical-align: top;
  max-width: calc(100% - 123px);
  box-sizing: border-box;
}

.laatste .info .naam {
  font-size: 19px;
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  line-height: 19px;
}

.laatste .info .naam .bold {
  font-weight: bold;
}

.laatste .info .tekst {
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  font-size: 15px;
  line-height: 24px;
}

.laatste .info .tekst span {
  margin-right: 6px;
}

input {
  text-transform: capitalize;
}

.score-blok {
  background: #2ecc71;
  padding: 20px;
  text-align: center;
  color: white;
  border-radius: 6px;
  margin-bottom: 20px;
}

.score-blok .titel {
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  font-size: 19px;
  font-weight: bold;
  text-align: center;
}

.score-blok .score-titel {
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  font-weight: bold;
  margin-top: 18px;
}

.score-blok .score {
  font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
  font-size: 50px;
  line-height: 50px;
  font-weight: 300;
}
</style>
