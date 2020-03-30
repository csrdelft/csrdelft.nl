<template>
	<div class="container stekpakket">
		<div class="row align-items-center">
			<div class="col-lg-2"></div>
			<div class="col-auto">
				<img src="/dist/images/beeldmerk.png" alt="C.S.R. Delft" class="beeldmerk">
			</div>
			<div class="col">
				<h1>De prijs van de stek</h1>
				<div class="subtitel">Stel uw stekpakket samen</div>
			</div>
			<div class="col-lg-2"></div>
		</div>
		<div class="row align-items-center">
			<div class="col-lg-2"></div>
			<div class="col-lg">
				<p>
					Hieronder kunt u uw eigen stekpakket samenstellen.
					U selecteert eerst een basispakket en past dit vervolgens aan naar uw smaak.
					De kosten van uw stekpakket worden maandelijks afgeschreven van uw CiviSaldo.
					Het stekpakket kunt u per maand bijstellen.
				</p>
			</div>
			<div class="col-lg-2"></div>
		</div>
		<div class="row equal">
			<div class="col-12"><h2>Kies een basispakket</h2></div>
			<div class="col-xl-3 col-sm-6" v-for="pakket in $props.basispakketten">
				<div class="pakket" :class="{ 'actief': gekozenBasispakket === pakket.titel }" @click="kiesBasispakket(pakket.titel, pakket.niveau)">
					<div class="titel">{{ pakket.titel }}</div>
					<div class="usp" v-for="usp in pakket.usps"><i class="fa fa-check"></i> {{ usp }}</div>
					<div class="prijs">
						<div class="getal">&euro; {{ pakket.euro }}<span>,{{ (pakket.centen < 10 ? '0' : '') + pakket.centen }}</span></div>
						<div class="per">per maand</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row" v-if="gekozenBasispakket" id="configuratie">
			<div class="col-12"><h2>Configureer uw stekpakket</h2></div>
		</div>
		<div class="row" v-if="gekozenBasispakket">
			<div class="col-lg-1 col-xl-2"></div>
			<div class="col-sm">
				<template v-for="(groep, index) in opties">
					<OptieWeergave :key="groep.groep + keyIndex" v-if="index < opties.length / 2" :index="index" @toggle="wijziging" />
				</template>
			</div>
			<div class="col-sm">
				<template v-for="(groep, index) in opties">
					<OptieWeergave :key="groep.groep + keyIndex" v-if="index >= opties.length / 2" :index="index" @toggle="wijziging" />
				</template>
			</div>
			<div class="col-lg-1 col-xl-2"></div>
		</div>
		<div class="row mt-4" v-if="gekozenBasispakket">
			<div class="col-lg-1 col-xl-2"></div>
			<div class="col-auto optellingTitel">
				Uw stekpakket:
			</div>
			<div class="col optelling">
				<div class="getal">&euro; {{ Math.floor(totaal) }}<span>,{{ (totaal - Math.floor(totaal) < 0.095 ? '0' : '') + Math.round((totaal - Math.floor(totaal)) * 100) }}</span></div>
				<div class="per">per maand</div>
			</div>
			<div class="col-lg-1 col-xl-2"></div>
		</div>
		<div class="row mt-4 mb-5" v-if="gekozenBasispakket">
			<div class="col-lg-1 col-xl-2"></div>
			<div class="col pb-5">
				<div class="bevestigen actief" v-if="gewijzigd && !laden" @click="slaOp">Sla stekpakket op</div>
				<div class="bevestigen laden" v-if="laden">Een ogenblik geduld...</div>
				<div class="bevestigen opgeslagen" v-if="!gewijzigd && !laden"><i class="fa fa-check"></i>&emsp;Opgeslagen</div>
			</div>
			<div class="col-lg-1 col-xl-2"></div>
		</div>
	</div>
</template>

<script lang="ts">
	import axios from 'axios';
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';
	import OptieWeergave from './OptieWeergave.vue';

	interface BasisPakket {
		titel: string;
		usps: string[];
		euro: number;
		centen: number;
		niveau: number;
	}

	export interface OptieGroep {
		groep: string;
		opties: { [key: string]: Optie };
	}

	export interface Optie {
		optie: string;
		vanaf: number;
		prijs: number;
		pre?: string;
		post?: string;
		actief: boolean;
	}

	@Component({
		components: {OptieWeergave},
	})
	export default class StekPakket extends Vue {
		@Prop()
		protected basispakketten: BasisPakket[];
		@Prop()
		protected opties: OptieGroep[];
		@Prop()
		protected opslaan: string;
		@Prop()
		protected basispakket: string;

		protected gekozenBasispakket: string = '';

		protected keyIndex = 0;
		protected gewijzigd = false;
		protected laden = false;
		protected totaal = 0;

		protected mounted() {
			this.gekozenBasispakket = this.basispakket;
			this.berekenTotaal();
		}

		protected kiesBasispakket(pakket: string, niveau: number) {
			if (this.laden) {
				return;
			}

			// Zet inbegrepen op aan
			for (const groep of this.opties) {
				for (const optie of Object.values(groep.opties)) {
					optie.actief = niveau >= optie.vanaf;
				}
			}

			this.gekozenBasispakket = pakket;
			this.keyIndex++;
			this.gewijzigd = true;
			this.berekenTotaal();

			this.$nextTick(() => {
				const offset = $('#configuratie').offset();
				if (offset) {
					$('html, body').animate({
						scrollTop: offset.top - 50,
					}, 800);
				}
			});
		}

		protected wijziging(prijsverschil: number) {
			this.gewijzigd = true;
			this.totaal = Math.round((this.totaal + prijsverschil) * 100) / 100;
		}

		protected berekenTotaal() {
			let totaal = 0;
			for (const groep of this.opties) {
				for (const optie of Object.values(groep.opties)) {
					if (optie.actief) {
						totaal += optie.prijs;
					}
				}
			}
			this.totaal = Math.round(totaal * 100) / 100;
		}

		protected getOptieLijst() {
			const lijst = [];
			for (const groep of this.opties) {
				for (const [key, optie] of Object.entries(groep.opties)) {
					if (optie.actief) {
						lijst.push(key);
					}
				}
			}
			return lijst;
		}

		protected slaOp() {
			this.laden = true;
			axios.post(this.opslaan, {
				basispakket: this.gekozenBasispakket,
				opties: this.getOptieLijst(),
			}).then(() => {
				this.laden = false;
				this.gewijzigd = false;
			}).catch(() => {
				this.laden = false;
			});
		}
	}
</script>

<style>
	.container {
		max-width: 1140px !important;
	}
</style>

<style scoped lang="scss">
	.stekpakket {
		font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;
	}

	.beeldmerk {
		width: 80px;
	}

	h1 {
		font-size: 20pt;
		font-weight: 600;
		line-height: 24pt;
		margin: 0;
	}

	.subtitel {
		font-size: 14pt;
		font-weight: 300;
	}

	p {
		font-size: 13pt;
		font-weight: 300;
	}

	h2 {
		text-align: center;
		font-size: 17pt;
		font-weight: 600;
		margin-bottom: 15px;
		margin-top: 30px;
	}

	.equal {
		display: flex;
		display: -webkit-flex;
		flex-wrap: wrap;
	}

	.pakket {
		border: 1px solid #3498db;
		border-radius: 5px;
		text-align: center;
		margin-bottom: 30px;
		min-height: calc(100% - 30px);
		background: white;
		color: black;
		cursor: pointer;
		padding: 0 15px 65px;
		position: relative;

		&:hover, &:focus, &.actief {
			background: #3498db;
			color: white;
			border-top-width: 0;

			.titel {
				background: white;
				color: #3498db;
				padding-top: 3px;
			}

			.usp i {
				color: white;
			}
		}

		.titel {
			margin-bottom: 15px;
			background: #3498db;
			border-radius: 0 0 3px 3px;
			color: white;
			font-size: 12pt;
			font-weight: 600;
			display: inline-block;
			padding: 2px 16px;
		}

		.usp {
			font-size: 10pt;
			font-weight: 300;
			text-align: left;
			line-height: 17pt;

			i {
				color: #2ECC71;
				margin-right: 8px;
			}
		}

		.prijs {
			position: absolute;
			width: 100%;
			left: 0;
			bottom: 10px;
			right: 0;

			.getal {
				font-size: 23pt;
				font-weight: 600;
				vertical-align: top;
				line-height: 24pt;

				span {
					font-weight: 400;
					font-size: 13pt;
					vertical-align: top;
					line-height: 17pt;
				}
			}

			.per {
				font-weight: 300;
				font-size: 11px;
				margin-top: -5px;
			}
		}
	}

	.optellingTitel {
		font-size: 20pt;
		font-weight: 600;
	}

	.optelling {
		text-align: right;

		.getal {
			font-size: 23pt;
			font-weight: 600;
			vertical-align: top;
			line-height: 24pt;

			span {
				font-weight: 400;
				font-size: 13pt;
				vertical-align: top;
				line-height: 17pt;
			}
		}

		.per {
			font-weight: 300;
			font-size: 11px;
			margin-top: -5px;
		}
	}

	.bevestigen {
		color: white;
		transition: background 0.2s ease-in-out;
		font-weight: 600;
		font-size: 13pt;
		text-align: center;
		padding: 5px 10px;
		border-radius: 5px;

		&.actief {
			background-color: #3498DB;
			cursor: pointer;
			&:hover {
				background-color: #58C1FF;
			}
		}

		&.laden {
			background-color: #58C1FF;
		}

		&.opgeslagen {
			background-color: #2ECC71;
		}
	}
</style>
