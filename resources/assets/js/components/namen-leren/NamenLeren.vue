<template>
	<div v-if="!started">
		<div class="row">
			<div class="col-sm-6">
				<b class="mb-1 block">Lichting(en)</b>
				<div>
					<input type="checkbox" id="alleLichtingen" v-model="alleLichtingen">
					<label for="alleLichtingen">Alle lichtingen</label>
				</div>
				<div v-for="lichting in lichtingen">
					<input type="checkbox" :id="'lichting' + lichting" :value="lichting" v-model="lichtingSelectie" v-if="!alleLichtingen">
					<input type="checkbox" :checked="alleLichtingen" disabled v-if="alleLichtingen">
					<label :for="'lichting' + lichting">{{ lichting }}</label>
				</div>
			</div>
			<div class="col-sm-6">
				<b class="mb-1 block">Verticale(n)</b>
				<div>
					<input type="checkbox" id="alleVerticalen" v-model="alleVerticalen">
					<label for="alleVerticalen">Alle verticalen</label>
				</div>
				<div v-for="verticale in verticalen">
					<input type="checkbox" :id="'verticale' + verticale" :value="verticale" v-model="verticaleSelectie" v-if="!alleVerticalen">
					<input type="checkbox" :checked="alleVerticalen" disabled v-if="alleVerticalen">
					<label :for="'verticale' + verticale">{{ verticale }}</label>
				</div>
			</div>
		</div>
		<b class="mt-3 mb-1 block">Antwoordmethode</b>
		<select v-model="antwoordMethode" class="form-control">
			<option value="voornaam">Voornaam</option>
			<option value="achternaam">Achternaam</option>
			<option value="combi">Voor- en achternaam</option>
		</select>
		<a href="#" @click.prevent="start" class="btn btn-primary btn-block mt-3" :class="{'disabled': !klaarVoorDeStart}">Start met {{ aantal }} {{ aantal === 1 ? 'lid' : 'leden' }}</a>
	</div>
	<div v-else-if="!finished">
		<div class="progress">
			<div class="correct" :style="{'width': percentageGoed + '%'}"></div>
			<div class="again" :style="{'width': percentageOpnieuw + '%'}"></div>
			<div class="wrong" :style="{'width': percentageFout + '%'}"></div>
		</div>
		<div class="pasfotoContainer">
			<div :style="{'background-image': 'url(/profiel/pasfoto/' + huidig.uid + '.jpg)'}" class="pasfoto"></div>
		</div>
		<b v-if="antwoordMethode === 'voornaam'" class="mb-1 block">Voornaam:</b>
		<b v-if="antwoordMethode === 'achternaam'" class="mb-1 block">Achternaam:</b>
		<b v-if="antwoordMethode === 'combi'" class="mb-1 block">Voor- en achtenraam:</b>
		<input type="text" class="form-control" v-model="ingevuld" @keydown.enter="controleer">
	</div>
</template>

<script>
	function shuffle(array) {
		let currentIndex = array.length, temporaryValue, randomIndex;

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
	}

	const preloaded = [];
	function preloadImage(url) {
		if (preloaded.includes(url)) return;
		preloaded.push(url);
		const img = new Image();
		img.src = url;
	}

	export default {
		name: 'NamenLeren',
		components: {},
		props: {
			leden: Array,
		},
		data: () => ({
			// Config
			alleLichtingen: false,
			alleVerticalen: true,
			lichtingSelectie: [],
			verticaleSelectie: [],
			antwoordMethode: 'voornaam',
			aantalPerKeer: 5,

			// Game state
			started: false,
			finished: false,
			goed: [],
			opnieuw: [],
			fout: [],
			todo: [],
			laatste: null,
			laatsteGoed: null,
			huidig: null,
			ingevuld: '',
		}),
		computed: {
			aantal() {
				return this.gefilterdeLeden.length;
			},
			gefilterdeLeden() {
				return this.leden.filter((lid) =>
					(this.alleLichtingen || this.lichtingSelectie.includes(lid.lichting))
					&& (this.alleVerticalen || this.verticaleSelectie.includes(lid.verticale))
				);
			},
			lichtingen() {
				return [...new Set(this.leden.map((lid) => lid.lichting))].sort();
			},
			verticalen() {
				return [...new Set(this.leden.map((lid) => lid.verticale))].sort();
			},
			klaarVoorDeStart() {
				return this.gefilterdeLeden.length > 0;
			},
			totaalAantal() {
				return this.todo.length + this.goed.length + this.opnieuw.length + this.fout.length;
			},
			percentageGoed() {
				return this.totaalAantal > 0 ? this.goed.length / this.totaalAantal * 100 : 0;
			},
			percentageOpnieuw() {
				return this.totaalAantal > 0 ? this.opnieuw.length / this.totaalAantal * 100 : 0;
			},
			percentageFout() {
				return this.totaalAantal > 0 ? this.fout.length / this.totaalAantal * 100 : 0;
			}
		},
		methods: {
			start: function () {
				if (!this.klaarVoorDeStart) return;
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
			},
			volgende() {
				let choice = this.fout.concat(this.todo.slice(0, Math.max(this.aantalPerKeer - this.fout.length, 0)));
				let pickable = choice.filter((lid) => choice.length === 1 || !this.huidig || lid.uid !== this.huidig.uid);
				if (pickable.length > 0) {
					for (const lid of pickable) {
						preloadImage('/profiel/pasfoto/' + lid.uid + '.jpg');
					}
					this.huidig = pickable[Math.floor(Math.random() * pickable.length)];
					this.ingevuld = '';
				} else {
					this.finished = true;
				}
			},
			controleer() {
				// Antwoord vormen
				let onderdelen = [];
				if (this.antwoordMethode === 'voornaam' || this.antwoordMethode === 'combi') {
					onderdelen.push(this.huidig.voornaam);
				}
				if (this.antwoordMethode === 'achternaam' || this.antwoordMethode === 'combi') {
					if (this.huidig.tussenvoegsel) onderdelen.push(this.huidig.tussenvoegsel);
					onderdelen.push(this.huidig.achternaam);
				}
				const antwoord = onderdelen.map(s => s.trim()).join(' ');

				// Antwoord checken
				this.laatste = this.huidig;
				this.laatsteGoed = antwoord.toLowerCase() === this.ingevuld.toLowerCase();

				// Verwijderen uit oude lijst en toevoegen aan nieuwe lijst
				let index = this.todo.findIndex((lid) => lid.uid === this.huidig.uid);
				if (index === -1) {
					// Fout lijst
					if (this.laatsteGoed) {
						index = this.fout.findIndex((lid) => lid.uid === this.huidig.uid);
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
			}
		},
	}
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
		transition: width ease-in-out .5s;
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
		width: 114px;
		height: 170px;
		margin: 30px auto;
		background: #ccc;
	}

	.pasfotoContainer .pasfoto {
		width: 114px;
		height: 170px;
		background-size: cover;
		background-repeat: no-repeat;
		background-position: center center;
	}
</style>
