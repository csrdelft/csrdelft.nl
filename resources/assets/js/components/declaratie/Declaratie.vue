<template>
	<div class="declaratie">
		<div class="field">
			<label for="categorie">Categorie</label>
			<select id="categorie" v-model="declaratie.categorie">
				<option disabled></option>
				<option :value="categorieID" v-for="(categorie,categorieID) in categorieen">{{ categorie }}</option>
			</select>
		</div>

		<div class="field">
			<label>Betaalwijze</label>
			<div>
				<input type="radio" id="C.S.R.-pas" value="C.S.R.-pas" v-model="declaratie.betaalwijze">
				<label for="C.S.R.-pas">Betaald met C.S.R.-pas</label>
			</div>
			<div>
				<input type="radio" id="voorgeschoten" value="voorgeschoten" v-model="declaratie.betaalwijze">
				<label for="voorgeschoten">Voorgeschoten</label>
			</div>
		</div>

		<div class="field" v-if="declaratie.betaalwijze === 'voorgeschoten'">
			<label>Terugstorten</label>
			<div>
				<input type="radio" id="eigenRekening" :value="true" v-model="declaratie.eigenRekening">
				<label for="eigenRekening">Naar eigen rekening</label>
			</div>
			<div>
				<input type="radio" id="nietEigenRekening" :value="false" v-model="declaratie.eigenRekening">
				<label for="nietEigenRekening">Naar andere rekening</label>
			</div>
		</div>

		<div class="field" v-if="declaratie.betaalwijze === 'voorgeschoten' && !declaratie.eigenRekening">
			<label for="rekening">IBAN</label>
			<input type="text" id="rekening" v-model="declaratie.rekening">
		</div>

		<div class="field" v-if="declaratie.betaalwijze === 'voorgeschoten' && !declaratie.eigenRekening || declaratie.betaalwijze === 'C.S.R.-pas'">
			<label for="tnv" v-if="declaratie.betaalwijze === 'voorgeschoten'">Ten name van</label>
			<label for="tnv" v-else>Bij bedrijf</label>
			<input type="text" id="tnv" v-model="declaratie.tnv">
		</div>

		<div class="bonnen bon-upload" v-if="bonUploaden || !heeftBonnen">
			<div class="inhoud">
				<div class="titel">Voeg je bonnen en facturen toe</div>
				<p>
					Upload je bon of factuur als PDF of goed leesbare foto.
					Neem daarna de bedragen van de bon of het factuur over.
				</p>
				<div class="buttons">
					<button class="blue">Kies bestand</button>
					<button class="open" @click="bonUploaden = false" v-if="heeftBonnen">Annuleren</button>
				</div>
			</div>
		</div>

		<div class="bonnen bonnen-weergave" v-if="!bonUploaden && heeftBonnen">
			<div class="lijst">
				<div class="bon" v-for="(bon,bonIndex) in declaratie.bonnen">
					<div class="bon-collapsed" @click="geselecteerdeBon = bonIndex" v-if="bonIndex !== geselecteerdeBon">
						<div class="left">
							<div class="title">Bon {{ bonIndex + 1 }}</div>
							<div class="date">{{ bon.datum }}</div>
						</div>
						<div class="right">
							<div class="title">&euro; {{ berekening(bon).totaalIncl|bedrag }}</div>
							<div class="btw">incl. btw</div>
						</div>
					</div>
					<div class="bon-selected" v-else>
						<div class="title">Bon {{ bonIndex + 1 }}</div>

						<div class="field">
							<label :for="'bon' + bonIndex + '_datum'">Datum</label>
							<input type="text" :id="'bon' + bonIndex + '_datum'" v-model="bon.datum" v-mask="'dd-mm-yyyy'">
						</div>

						<div class="bon-regels">
							<div class="regels-row">
								<label>Omschrijving</label>
								<label>Bedrag</label>
								<label>Btw</label>
								<div></div>
							</div>
							<div class="regels-row" v-for="(regel, index) in bon.regels">
								<div class="field">
									<input type="text" v-model="regel.omschrijving">
								</div>
								<div class="field">
									<input type="text" v-model="regel.bedrag" v-mask="{'alias': 'numeric', 'groupSeparator': ',', 'digits': 2, 'digitsOptional': false, 'placeholder': '0'}">
								</div>
								<div class="field">
									<select v-model="regel.btw">
										<option value="" disabled></option>
										<option value="incl. 9%">incl. 9%</option>
										<option value="incl. 21%">incl. 21%</option>
										<option value="excl. 9%">excl. 9%</option>
										<option value="excl. 21%">excl. 21%</option>
										<option value="geen: 0%">geen: 0%</option>
									</select>
								</div>
								<div v-if="bon.regels.length > 1" class="trash" @click="regelVerwijderen(bon, index)">
									<i class="fa fa-trash-alt"></i>
								</div>
							</div>
							<div class="regels-row nieuw" @click="nieuweRegel(bon)">
								<div class="field">
									<input type="text" disabled>
								</div>
								<div class="field">
									<input type="text" disabled>
								</div>
								<div class="field">
									<select disabled>
										<option value=""></option>
									</select>
								</div>
								<div class="add">
									<i class="fa fa-plus-circle"></i>
								</div>
							</div>
							<div class="regels-row totaal streep">
								<div class="onderdeel">Totaal excl. btw</div>
								<div class="bedrag">{{ berekening(bon).totaalExcl|bedrag }}</div>
							</div>
							<div class="regels-row totaal" v-if="berekening(bon).btw[9] > 0">
								<div class="onderdeel">Btw 9%</div>
								<div class="bedrag">{{ berekening(bon).btw[9]|bedrag }}</div>
							</div>
							<div class="regels-row totaal" v-if="berekening(bon).btw[21] > 0">
								<div class="onderdeel">Btw 21%</div>
								<div class="bedrag">{{ berekening(bon).btw[21]|bedrag }}</div>
							</div>
							<div class="regels-row totaal totaalBold">
								<div class="onderdeel">Totaal incl. btw</div>
								<div class="bedrag">{{ berekening(bon).totaalIncl|bedrag }}</div>
							</div>
						</div>
					</div>
				</div>
				<div class="nieuwe-bon" @click="bonUploaden = true">
					<i class="fa fa-plus-circle"></i>
				</div>
			</div>
			<div class="voorbeeld">
				<iframe :src="huidigeBon.bestandsnaam"></iframe>
			</div>
		</div>

		<div class="totaal" v-if="totaal > 0">
			<div class="left">Totaal</div>
			<div class="right">
				<div class="title">&euro; {{ totaal|bedrag }}</div>
				<div class="btw">incl. btw</div>
			</div>
		</div>

		<div class="field">
			<label for="opmerkingen">Opmerkingen</label>
			<textarea id="opmerkingen" v-model="declaratie.opmerkingen"></textarea>
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop} from 'vue-property-decorator';

	const VueInputMask = require('vue-inputmask').default;

	Vue.use(VueInputMask);

	interface Declaratie {
		categorie?: number;
		betaalwijze?: 'C.S.R.-pas' | 'voorgeschoten';
		eigenRekening?: boolean;
		rekening?: string;
		tnv?: string;
		bonnen?: Bon[];
		opmerkingen: string;
		status: 'concept' | 'ingediend' | 'afgekeurd' | 'goedgekeurd';
	}

	interface Bon {
		bestandsnaam: string;
		datum: string;
		regels: Regel[];
	}

	interface Regel {
		omschrijving: string;
		bedrag: number | null;
		btw: '' | 'incl. 9%' | 'incl. 21%' | 'excl. 9%' | 'excl. 21%';
	}

	const legeRegel: () => Regel = () => ({
		omschrijving: '',
		bedrag: null,
		btw: '',
	});

	const legeDeclaratie: () => Declaratie = () => ({
		opmerkingen: '',
		eigenRekening: true,
		status: 'concept',
		bonnen: [ // TODO: Verwijderen
			{
				bestandsnaam: 'https://media-cdn.tripadvisor.com/media/photo-s/0a/de/cc/6f/addition.jpg',
				datum: '',
				regels: [
					{
						omschrijving: 'Pizza',
						bedrag: 25.00,
						btw: 'incl. 9%',
					},
				],
			},
			{
				bestandsnaam: 'https://media-cdn.tripadvisor.com/media/photo-s/0a/de/cc/6f/addition.jpg',
				datum: '',
				regels: [
					{
						omschrijving: 'Pizza',
						bedrag: 25.00,
						btw: 'incl. 9%',
					},
				],
			},
		],
	});

	@Component({
		filters: {
			bedrag(value: number) {
				const text = value.toString();
				const split = text.split('.');
				if (split.length === 1) {
					return text + ',00';
				} else {
					return split[0] + ',' + split[1].padEnd(2, '0');
				}
			},
		},
	})
	export default class DeclaratieVue extends Vue {
		@Prop()
		private type: 'nieuw' | 'bewerken';
		@Prop()
		private categorieen: Record<number, string>;
		@Prop({default: legeDeclaratie})
		private declaratie: Declaratie;

		private bonUploaden: boolean = true;
		private geselecteerdeBon: number = 0;

		private get heeftBonnen() {
			return this.declaratie.bonnen && this.declaratie.bonnen.length > 0;
		}

		private get huidigeBon() {
			if (!this.heeftBonnen) {
				return null;
			} else {
				this.geselecteerdeBon = Math.min(this.declaratie.bonnen!.length - 1, this.geselecteerdeBon);
				return this.declaratie.bonnen![this.geselecteerdeBon];
			}
		}

		public nieuweRegel(bon: Bon) {
			bon.regels.push(legeRegel());
		}

		public regelVerwijderen(bon: Bon, regel: number) {
			bon.regels.splice(regel, 1);
		}

		public get totaal() {
			let totaal = 0;
			for (let bon of this.declaratie.bonnen) {
				totaal += this.berekening(bon).totaalIncl;
			}
			return totaal;
		}

		public berekening(bon: Bon): { totaalExcl: number, totaalIncl: number, btw: { 0: number, 9: number, 21: number } } {
			const ret = {
				totaalExcl: 0,
				totaalIncl: 0,
				btw: {
					0: 0,
					9: 0,
					21: 0,
				},
			};

			for (const regel of bon.regels) {
				if (regel.btw && regel.bedrag) {
					regel.bedrag = parseFloat(regel.bedrag.toString());
					const incl = regel.btw.substr(0, 4) === 'incl';
					const percentage = parseInt(regel.btw.substr(6).replace('%', ''), 10);
					const perunage = percentage / 100;

					if (incl) {
						ret.totaalExcl += regel.bedrag / (1 + perunage);
						ret.btw[percentage] += regel.bedrag / (1 + perunage) * perunage;
						ret.totaalIncl += regel.bedrag;
					} else {
						ret.totaalExcl += regel.bedrag;
						ret.btw[percentage] += regel.bedrag * perunage;
						ret.totaalIncl += regel.bedrag * (1 + perunage);
					}
				}
			}

			function round(toRound: number) {
				return Math.round((toRound + Number.EPSILON) * 100) / 100;
			}

			return {
				totaalExcl: round(ret.totaalExcl),
				totaalIncl: round(ret.totaalIncl),
				btw: {
					0: round(ret.btw[0]),
					9: round(ret.btw[9]),
					21: round(ret.btw[21]),
				},
			};
		}
	}
</script>

<style scoped lang="scss">
	.declaratie {
		font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
		font-size: 1rem;
	}

	.field {
		& > label {
			display: block;
			color: #3C3C3C;
			font-weight: 600;
			margin-bottom: 4px;
		}

		input[type=text],
		input[type=date],
		select,
		textarea {
			border: 1px solid #868686;
			outline: none;
			padding: 0.4rem;
			font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;
			font-size: 1.2rem;
			font-weight: 300;
			border-radius: 4px;
			display: block;
			width: 100%;
		}

		[type=radio] + label {
			font-weight: 300;
			font-size: 1.2rem;
			margin-left: 6px;
		}

		& + .field {
			margin-top: 11px;
		}
	}

	.bonnen {
		border-radius: 6px;
		border: 1px solid #D0D0D0;
		margin: 30px 0;

		&.bonnen-weergave {
			display: grid;
			grid-template-columns: 550px auto;
			height: 400px;
			overflow: hidden;

			.lijst {
				height: 100%;
				overflow-y: auto;
				background: #F2F2F2;

				.nieuwe-bon {
					text-align: center;
					font-size: 21px;
					padding: 14px 0 33px;
					color: #2ECC71;
					cursor: pointer;
				}

				.bon {
					border-bottom: 1px solid #D0D0D0;

					.bon-collapsed {
						padding: 12px 25px;
						background: #FAFAFA;
						cursor: pointer;
						display: flex;
						justify-content: space-between;

						.title {
							font-size: 16px;
							color: #4A4A4A;
							margin-bottom: 0;
						}

						.date {
							font-weight: 300;
							font-size: 15px;
						}

						.right {
							.btw {
								margin-top: -3px;
								font-size: 13px;
								font-weight: 300;
								text-align: right;
							}
						}
					}

					.bon-selected {
						padding: 21px 25px;
						background: white;
					}

					.bon-regels {
						margin-top: 11px;
					}

					.regels-row {
						display: grid;
						grid-template-columns: 3fr 1fr 1fr 15px;
						grid-column-gap: 6px;
						margin-top: 9px;

						.field + .field {
							margin-top: 0;
						}

						.trash, .add {
							line-height: 33px;
							text-align: right;
							cursor: pointer;

							&.trash {
								color: #676767;
							}

							&.add {
								color: #2ECC71;
							}
						}

						&.nieuw {
							cursor: pointer;

							.field {
								position: relative;

								&:before {
									content: '';
									position: absolute;
									top: 0;
									right: 0;
									bottom: 0;
									left: 0;
									background: rgba(255, 255, 255, 0.65);
								}
							}

							input:disabled, select:disabled {
								background: white;
							}
						}

						&.totaal {
							font-size: 15px;
							font-weight: 300;

							.onderdeel {
								text-align: right;
								padding-right: 20px;
							}

							&.streep {
								div {
									padding-top: 9px;
								}

								.bedrag {
									border-top: 1px solid #C7C7C7;
								}
							}

							.bedrag {
								position: relative;
								text-align: right;

								&:before {
									content: '€';
									position: absolute;
									left: 0;
								}
							}

							&.totaalBold {
								font-weight: 600;
							}
						}
					}

					.title {
						font-size: 18px;
						font-weight: 600;
						margin-bottom: 5px;
					}

					label {
						font-size: 11px;
					}

					input {
						font-size: 1.1rem;
					}
				}
			}

			.voorbeeld {
				background: #545454;

				iframe {
					width: 100%;
					height: 100%;
					border: none;
				}
			}
		}

		&.bon-upload {
			background: linear-gradient(135deg, #ffffff, #f4f6f9 41%, #eff3f6);
			position: relative;
			overflow: hidden;
			min-height: 400px;
			padding: 110px;

			.inhoud {
				position: relative;
			}

			.titel {
				font-size: 2rem;
				font-weight: 600;
			}

			p {
				font-size: 1.5rem;
				font-weight: 300;
				max-width: 360px;
				line-height: 1.4;
				margin-top: 9px;
				margin-bottom: 14px;
			}

			.buttons {
				button {
					width: 110px;
					border-radius: 3px;
					-webkit-appearance: none;
					padding: 6px 0;
					border: none;
					margin-right: 10px;
					margin-top: 5px;

					&.blue {
						background: #00087B;
						color: white;
						font-weight: 600;

						&:hover {
							background: #3498db;
						}
					}

					&.open {
						border: 1px solid #D0D0D0;
						color: #898989;
						font-weight: 600;

						&:hover {
							border: 1px solid #898989;
							color: black;
						}
					}
				}
			}

			&:before {
				content: '';
				position: absolute;
				background: url("../../../images/declaratie.svg") right bottom no-repeat;
				background-size: auto 210px;
				left: 0;
				top: 0;
				right: 0;
				bottom: 0;
			}
		}
	}

	.totaal {
		display: grid;
		grid-template-columns: 1fr 1fr;

		.left {
			font-size: 21px;
			font-weight: 600;
		}

		.right {
			text-align: right;

			.title {
				font-size: 27px;
				font-weight: 600;
				color: black;
				margin-bottom: 0;
			}

			.btw {
				margin-top: -6px;
				font-size: 16px;
				font-weight: 300;
			}
		}
	}
</style>
