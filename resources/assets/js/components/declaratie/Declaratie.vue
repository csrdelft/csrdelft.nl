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
				<div class="bon" v-for="(bon,index) in declaratie.bonnen">
					<div class="title">Bon {{ index + 1 }}</div>

					<div class="field">
						<label :for="'bon' + index + '_datum'">Datum</label>
						<input type="text" :id="'bon' + index + '_datum'" v-model="bon.datum" v-mask="'dd-mm-yyyy'">
					</div>

					<div class="regels">
						<div class="regels-row">
							<label>Omschrijving</label>
							<label>Bedrag</label>
							<label>Btw</label>
							<div></div>
						</div>
						<div class="regels-row" v-for="regel in bon.regels">
							<div class="field">
								<input type="text" v-model="regel.omschrijving">
							</div>
							<div class="field">
								<input type="text" v-model="regel.bedrag" v-mask="{'alias': 'numeric', 'groupSeparator': ',', 'digits': 2, 'digitsOptional': false, 'prefix': '€ ', 'placeholder': '0'}">
							</div>
							<div class="field">
								<select v-model="regel.btw">

								</select>
							</div>
							<div>
								<i class="fa fa-trash"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="voorbeeld">
				<iframe :src="huidigeBon.bestandsnaam"></iframe>
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
		bedrag: number;
		btw: 'incl. 9%' | 'incl. 21%' | 'excl. 9%' | 'excl. 21%';
	}

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

	@Component({})
	export default class DeclaratieVue extends Vue {
		@Prop()
		private type: 'nieuw' | 'bewerken';
		@Prop()
		private categorieen: Record<number, string>;
		@Prop({default: legeDeclaratie})
		private declaratie: Declaratie;

		private bonUploaden: boolean = true;
		private geselecteerdeRegel: number = 0;

		private get heeftBonnen() {
			return this.declaratie.bonnen && this.declaratie.bonnen.length > 0;
		}

		private get huidigeBon() {
			if (!this.heeftBonnen) {
				return null;
			} else {
				this.geselecteerdeRegel = Math.max(this.declaratie.bonnen!.length - 1, this.geselecteerdeRegel);
				return this.declaratie.bonnen![this.geselecteerdeRegel];
			}
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

			.lijst {
				.bon {
					padding: 21px 25px;

					.regels-row {
						display: grid;
						grid-template-columns: 3fr 1fr 1fr 15px;
						grid-column-gap: 6px;

						.field + .field {
							margin-top: 0;
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
			min-height: 210px;
			padding: 40px;

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
</style>
