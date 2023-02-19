<template>
  <div>
    <div v-if="finished" class="score-blok">
      <div class="titel">
        {{ titel }}
      </div>
      <div class="score-titel">Jouw score:</div>
      <div class="score">{{ Math.round(percentageGoed) }}%</div>
    </div>
    <div v-if="!started">
      <div class="row">
        <div class="col-sm-6">
          <strong class="mb-1 block">Lichting(en)</strong>
          <div>
            <input
              id="alleLichtingen"
              v-model="alleLichtingen"
              type="checkbox"
            />
            <label for="alleLichtingen">Alle lichtingen</label>
          </div>
          <div v-for="lichting in lichtingen" :key="lichting">
            <input
              v-if="!alleLichtingen"
              :id="'lichting' + lichting"
              v-model="lichtingSelectie"
              type="checkbox"
              :value="lichting"
            />
            <input
              v-if="alleLichtingen"
              type="checkbox"
              :checked="alleLichtingen"
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
              v-model="alleVerticalen"
              type="checkbox"
            />
            <label for="alleVerticalen">Alle verticalen</label>
          </div>
          <div v-for="verticale in verticalen" :key="verticale">
            <input
              v-if="!alleVerticalen"
              :id="'verticale' + verticale"
              v-model="verticaleSelectie"
              type="checkbox"
              :value="verticale"
            />
            <input
              v-if="alleVerticalen"
              type="checkbox"
              :checked="alleVerticalen"
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
            v-model="verbergOnderkant"
            type="checkbox"
          />
          <label for="verbergOnderkant">Voorkom leesbare namen op foto's</label>
        </div>
      </div>
      <strong class="mt-3 mb-1 block">Antwoordmethode</strong>
      <select v-model="antwoordMethode" class="form-control">
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
        >Start met {{ aantal }} {{ aantal === 1 ? 'lid' : 'leden' }}</a
      >
    </div>
    <div v-else-if="!finished">
      <div class="progress">
        <div class="correct" :style="{ width: percentageGoed + '%' }" />
        <div class="again" :style="{ width: percentageOpnieuw + '%' }" />
        <div class="wrong" :style="{ width: percentageFout + '%' }" />
      </div>
      <div v-if="laatste" class="laatste" :class="{ goed: laatsteGoed }">
        <img :src="'/profiel/pasfoto/' + laatste.uid + '.jpg'" alt="" />
        <div class="info">
          <div class="naam">
            <span
              :class="{
                bold:
                  antwoordMethode === 'voornaam' || antwoordMethode === 'combi',
              }"
              >{{ laatste.voornaam }}</span
            >
            <span
              :class="{
                bold:
                  antwoordMethode === 'achternaam' ||
                  antwoordMethode === 'combi' ||
                  antwoordMethode === 'civi',
              }"
              >{{ laatste.tussenvoegsel }} {{ laatste.achternaam }}</span
            >
            <span
              v-if="laatste.postfix"
              :class="{ bold: antwoordMethode === 'civi' }"
              >{{ laatste.postfix }}</span
            >
          </div>
          <div class="tekst">
            <span>{{ laatste.lichting }}</span>
            <span v-if="laatste.verticale && laatste.verticale !== 'Geen'">{{
              laatste.verticale
            }}</span>
            <span>{{ laatste.studie }}</span>
          </div>
        </div>
        <Icon icon="check" v-if="laatsteGoed" />
        <Icon icon="xmark" v-else />
      </div>
      <div
        class="pasfotoContainer"
        :class="{ onderkantVerbergen: verbergOnderkant }"
      >
        <div
          :style="{
            'background-image': 'url(/profiel/pasfoto/' + huidig.uid + '.jpg)',
          }"
          class="pasfoto"
        />
      </div>
      <strong v-if="antwoordMethode === 'voornaam'" class="mb-1 block"
        >Voornaam:</strong
      >
      <strong v-if="antwoordMethode === 'achternaam'" class="mb-1 block"
        >Achternaam:</strong
      >
      <strong v-if="antwoordMethode === 'combi'" class="mb-1 block"
        >Voor- en achternaam:</strong
      >
      <strong v-if="antwoordMethode === 'civi'" class="mb-1 block"
        >Achternaam en achtervoegsel:</strong
      >
      <input
        type="text"
        class="form-control"
        :value="ingevuld"
        autofocus
        @input="ingevuld = $event.target.value"
        @keydown.enter="controleer"
      />
    </div>
  </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import Icon from '../common/Icon.vue';

const shuffle = <T>(array: T[]) => {
  let currentIndex = array.length;
  let temporaryValue;
  let randomIndex;

  // While there remain elements to shuffle...
  while (0 !== currentIndex) {
    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;

    // And swap it with the current element.
    temporaryValue = array[currentIndex];
    array[currentIndex] = array[randomIndex];
    array[randomIndex] = temporaryValue;
  }

  return array;
};

const preloaded: string[] = [];

const preloadImage = (url: string) => {
  if (preloaded.includes(url)) {
    return;
  }
  preloaded.push(url);
  const img = new Image();
  img.src = url;
};

const uniq: <T>(arr: T[]) => T[] = (arr) => {
  return Array.from(new Set(arr));
};

interface Lid {
  uid: string;
  lichting: string;
  verticale: string;
  tussenvoegsel: string;
  achternaam: string;
  voornaam: string;
  postfix: string;
}

export default defineComponent({
  components: { Icon },
  props: {
    leden: {
      required: true,
      type: Array as PropType<Lid[]>,
    },
  },
  data: () => ({
    // Config
    alleLichtingen: false,
    alleVerticalen: true,
    lichtingSelectie: [] as string[],
    verticaleSelectie: [] as string[],
    antwoordMethode: 'voornaam',
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
  }),
  computed: {
    aantal() {
      return this.gefilterdeLeden.length;
    },
    gefilterdeLeden() {
      return this.leden.filter(
        (lid: Lid) =>
          (this.alleLichtingen ||
            this.lichtingSelectie.includes(lid.lichting)) &&
          (this.alleVerticalen ||
            this.verticaleSelectie.includes(lid.verticale))
      );
    },
    lichtingen() {
      return uniq(this.leden.map((lid) => lid.lichting)).sort();
    },
    verticalen() {
      return uniq(this.leden.map((lid) => lid.verticale)).sort();
    },
    klaarVoorDeStart() {
      return this.gefilterdeLeden.length > 0;
    },
    totaalAantal() {
      return (
        this.todo.length +
        this.goed.length +
        this.opnieuw.length +
        this.fout.length
      );
    },
    percentageGoed() {
      return this.totaalAantal > 0
        ? (this.goed.length / this.totaalAantal) * 100
        : 0;
    },
    percentageOpnieuw() {
      return this.totaalAantal > 0
        ? (this.opnieuw.length / this.totaalAantal) * 100
        : 0;
    },
    percentageFout() {
      return this.totaalAantal > 0
        ? (this.fout.length / this.totaalAantal) * 100
        : 0;
    },
  },
  methods: {
    start() {
      if (!this.klaarVoorDeStart) {
        return;
      }
      this.started = true;
      this.goed = [];
      this.opnieuw = [];
      this.fout = [];
      this.todo = this.gefilterdeLeden;
      shuffle(this.todo);
      this.huidig = null;
      this.laatste = null;
      this.finished = false;
      this.volgende();
      this.titel = this.bouwTitel();
      document.title = `C.S.R. Delft - Namen ${this.titel} leren`;
      window.scrollTo(0, 0);
    },
    volgende() {
      const choice = this.fout.concat(
        this.todo.slice(0, Math.max(this.aantalPerKeer - this.fout.length, 0))
      );
      const pickable = choice.filter(
        (lid) =>
          choice.length === 1 || !this.huidig || lid.uid !== this.huidig.uid
      );
      if (pickable.length > 0) {
        for (const lid of pickable) {
          preloadImage('/profiel/pasfoto/' + lid.uid + '.jpg');
        }
        this.huidig = pickable[Math.floor(Math.random() * pickable.length)];
        this.ingevuld = '';
      } else {
        this.finished = true;
        this.started = false;
      }
    },
    controleer() {
      if (this.huidig == null) {
        throw new Error('huidig niet gezet');
      }
      // Antwoord vormen
      const onderdelen = [];
      if (
        this.antwoordMethode === 'voornaam' ||
        this.antwoordMethode === 'combi'
      ) {
        onderdelen.push(this.huidig.voornaam);
      }
      if (
        this.antwoordMethode === 'achternaam' ||
        this.antwoordMethode === 'combi' ||
        this.antwoordMethode === 'civi'
      ) {
        if (this.huidig.tussenvoegsel) {
          onderdelen.push(this.huidig.tussenvoegsel);
        }
        onderdelen.push(this.huidig.achternaam);
      }
      if (this.antwoordMethode === 'civi') {
        if (this.huidig.postfix) {
          onderdelen.push(this.huidig.postfix);
        }
      }
      const antwoord = onderdelen.map((s) => s.trim()).join(' ');

      // Antwoord checken
      this.laatste = this.huidig;
      this.laatsteGoed =
        antwoord.toLowerCase().replace('.', '') ===
        this.ingevuld.toLowerCase().replace('.', '');

      // Verwijderen uit oude lijst en toevoegen aan nieuwe lijst
      let index = this.todo.findIndex((lid) => lid.uid === this.huidig?.uid);
      if (index === -1) {
        // Fout lijst
        if (this.laatsteGoed) {
          index = this.fout.findIndex((lid) => lid.uid === this.huidig?.uid);
          this.fout.splice(index, 1);
          this.opnieuw.push(this.huidig);
        }
      } else {
        // Te doen lijst
        this.todo.splice(index, 1);
        if (this.laatsteGoed) {
          this.goed.push(this.huidig);
        } else {
          this.fout.push(this.huidig);
        }
      }

      this.volgende();
    },
    bouwTitel() {
      if (this.alleLichtingen && this.alleVerticalen) {
        return 'Alle leden';
      }

      let titel = '';
      if (!this.alleLichtingen) {
        this.lichtingSelectie.sort();
        titel += 'Lichting ';
        titel += this.lichtingSelectie
          .slice(0, this.lichtingSelectie.length - 1)
          .join(', ');
        if (this.lichtingSelectie.length > 1) {
          titel += ' & ';
        }
        titel += this.lichtingSelectie[this.lichtingSelectie.length - 1];
      }
      if (!this.alleVerticalen) {
        if (titel) {
          titel += ', ';
        }
        this.verticaleSelectie.sort();
        titel += this.verticaleSelectie
          .slice(0, this.verticaleSelectie.length - 1)
          .join(', ');
        if (this.verticaleSelectie.length > 1) {
          titel += ' & ';
        }
        titel += this.verticaleSelectie[this.verticaleSelectie.length - 1];
      }

      return titel;
    },
  },
});
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
