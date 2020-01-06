<template>
	<div class="ketzertovenaar">

		<Stap
			title="In welke categorie valt je activiteit?"
			:step="1">

			<SelectButtons
				name="type"
				:options="types"
				v-model="event.type"
				v-on:done="gotoStep(2, true)">
			</SelectButtons>

		</Stap>

		<Stap
			title="Wat is de titel van je activiteit?"
			:step="2"
			:show-done="this.event.title.length > 0"
			v-on:done="gotoStep(3, true)">

			<TextInput
				name="title"
				:max-length="100"
				hint="Titel"
				v-model="event.title"
				v-on:next="event.title.length > 0 ? gotoStep(3, true) : null"
				:error="event.title.length === 0 ? 'Geef je activiteit een titel' : ''">
			</TextInput>

		</Stap>

		<Stap
			title="Beschrijf je activiteit"
			:step="3"
			:show-done="this.event.shortDescription.length > 0"
			v-on:done="gotoStep(4)">

			<TextInput
				name="shortDescription"
				:max-length="250"
				hint="Korte beschrijving"
				:multiple-lines="5"
				v-model="event.shortDescription"
				:error="event.shortDescription.length === 0 ? 'Omschrijf kort je activiteit' : ''">
			</TextInput>

			<TextInput
				name="readMore"
				hint="Lees meer"
				:multiple-lines="5"
				v-model="event.readMore">
			</TextInput>

		</Stap>

		<Stap
			title="Wanneer is je activiteit?"
			:step="4"
			:show-done="event.calendarData && (event.calendarData.selectedDate !== false || event.calendarData.dateRange.end !== false)"
			v-on:done="gotoStep(5)">

			<Toggle
				name="multipleDays"
				question="Activiteit duurt meerdere dagen"
				v-model="event.multipleDays">
			</Toggle>

			<v-date-picker
				:mode="event.multipleDays ? 'range' : 'single'"
				v-model="event.calendarData"
				is-inline
				is-expanded
			></v-date-picker>
		</Stap>

		<Stap
			title="Hoe laat?"
			:step="5"
			:show-done="event.entireDay || (validStartTime(true) && validEndTime(true))"
			v-on:done="gotoStep(6, true)">

			<Toggle
				name="heleDay"
				question="Activiteit duurt hele dag"
				v-model="event.entireDay">
			</Toggle>

			<template v-if="!event.entireDay">
				<div class="input-half">
					<TextInput
						name="startTime"
						mask="HH:MM[:ss]"
						mask-placeholder="__:__[:__]"
						hint="Van"
						v-model="event.startTime"
						v-on:next="validStartTime(true) && validEndTime(true) ? gotoStep(6, true) : null"
						:error="validStartTime()">
					</TextInput>
				</div>

				<div class="input-half">
					<TextInput
						name="endTime"
						mask="HH:MM[:ss]"
						mask-placeholder="__:__[:__]"
						hint="Tot"
						v-model="event.endTime"
						v-on:next="validStartTime(true) && validEndTime(true) ? gotoStep(6, true) : null"
						:error="validEndTime()">
					</TextInput>
				</div>
			</template>
		</Stap>

		<Stap
			title="Waar?"
			:step="6"
			:show-done="this.event.location.length > 0"
			v-on:done="gotoStep(7)">

			<TextInput
				name="location"
				hint="Locatie"
				v-model="event.location"
				v-on:next="event.location.length > 0 ? gotoStep(7, true) : null"
				:error="event.location.length === 0 ? 'Geef aan waar je evenement plaats zal vinden' : ''">
			</TextInput>

		</Stap>

		<Stap
			title="Inketzen"
			:step="7"
			:show-done="true"
			v-on:done="gotoStep(8)">

			<Toggle
				name="canEnter"
				question="Leden mogen zichzelf inketzen"
				v-model="event.canEnter">
			</Toggle>

			<div v-if="event.canEnter" class="subOptions">
				<Toggle
					name="enterStart"
					question="Ketzer pas open stellen na een bepaald moment"
					v-model="event.enterStart">
				</Toggle>

				<div class="moment" v-if="event.enterStart">
					<div class="input-half">
						<label>Ketzer openen op</label>
						<DateInput name="enterStart" v-model="event.enterStartMoment"></DateInput>
					</div>

					<div class="input-half">
						<TextInput
							name="enterStartMomentTime"
							mask="HH:MM[:ss]"
							mask-placeholder="__:__[:__]"
							hint="Tijd"
							v-model="event.enterStartMomentTime"
							:error="validTime(event.enterStartMomentTime) ? null : 'Ongeldige tijd'">
						</TextInput>
					</div>
				</div>

				<Toggle
					name="enterEnd"
					question="Ketzer sluiten na een bepaald moment"
					@input="prepareEnterEnd()"
					v-model="event.enterEnd">
				</Toggle>

				<div class="moment" v-if="event.enterEnd">
					<div class="input-half">
						<label>Ketzer sluiten op</label>
						<DateInput ref="enterEndmoment" name="enterEnd" v-model="event.enterEndMoment"></DateInput>
					</div>

					<div class="input-half">
						<TextInput
							name="enterEndMomentTime"
							mask="HH:MM[:ss]"
							mask-placeholder="__:__[:__]"
							hint="Tijd"
							v-model="event.enterEndMomentTime"
							:error="validTime(event.enterEndMomentTime) ? null : 'Ongeldige tijd'">
						</TextInput>
					</div>
				</div>
			</div>

			<Toggle
				name="canExit"
				question="Leden mogen zichzelf uitketzen"
				v-model="event.canExit">
			</Toggle>

			<div v-if="event.canExit" class="subOptions">
				<Toggle
					name="exitEnd"
					question="Uitketzen niet toestaan na een bepaald moment"
					@input="prepareExitEnd()"
					v-model="event.exitEnd">
				</Toggle>

				<div class="moment" v-if="event.exitEnd">
					<div class="input-half">
						<label>Uitketzen toestaan tot</label>
						<DateInput ref="exitEndMoment" name="exitEnd" v-model="event.exitEndMoment"></DateInput>
					</div>

					<div class="input-half">
						<TextInput
							name="exitEndMomentTime"
							mask="HH:MM[:ss]"
							mask-placeholder="__:__[:__]"
							hint="Tijd"
							v-model="event.exitEndMomentTime"
							:error="validTime(event.exitEndMomentTime) ? null : 'Ongeldige tijd'">
						</TextInput>
					</div>
				</div>
			</div>

			<Toggle
				name="hasLimit"
				question="Er mag maar een beperkt aantal leden inketzen"
				v-model="event.hasLimit">
			</Toggle>

			<div v-if="event.hasLimit">
				<TextInput
					name="limit"
					hint="Maximum"
					v-model="event.limit"
					:number="true"
					:error="validLimit(event.limit) ? null : 'Vul een geldig aantal in'">
				</TextInput>
			</div>
		</Stap>

		<Stap
			title="Wie mag er komen?"
			:step="8"
			:show-done="true"
			v-on:done="gotoStep(9)">

			<IcoonKiezer
				name="hasPermission"
				:options="{
					'iedereen': {
						title: 'Iedereen',
						description: 'Geen beperking stellen op wie er mag komen. Iedereen kan zich inketzen.',
						image: icons.IedereenIcoon,
						imageSelected: icons.IedereenSelectIcoon,
					},
					'groep': {
						title: 'Geselecteerde groep',
						description: 'Alleen leden die aan geselecteerde criteria voldoen, mogen zichzelf inketzen.',
						image: icons.GroepIcoon,
						imageSelected: icons.GroepSelectIcoon,
					},
				}"
				v-model="event.hasPermission">
			</IcoonKiezer>

			<template v-if="event.hasPermission === 'groep'">
				<div class="explain">
					<div class="heading">Selecteer de groepen die mogen komen</div>
					<p>Ieder lid dat aan &eacute;&eacute;n van deze criteria voldoet, zal zichzelf kunnen inketzen.</p>
				</div>
				<RechtenBouwer v-model="event.permission"></RechtenBouwer>
			</template>
		</Stap>

		<Stap
			title="Wat wil je weten?"
			:step="9"
			:show-done="true"
			v-on:done="gotoStep(10)">

			<IcoonKiezer
				name="hasChoice"
				:options="{
					'invulveld': {
						title: 'Vrije opmerking',
						description: 'Leden die zich inketzen kunnen zelf een opmerking in een tekstvak invullen.',
						image: icons.InvulIcoon,
						imageSelected: icons.InvulSelectIcoon,
					},
					'keuzelijst': {
						title: 'Keuzelijst(en)',
						description: 'Leden die zich inketzen moeten een keuze maken uit de vooraf gedefinieerde opties.',
						image: icons.KeuzeIcoon,
						imageSelected: icons.KeuzeSelectIcoon,
					},
				}"
				v-model="event.hasChoice">
			</IcoonKiezer>

			<template v-if="event.hasChoice === 'keuzelijst'">
				<div class="explain">
					<div class="heading">Maak &eacute;&eacute;n of meerdere keuzelijsten</div>
					<p>Iedereen die zich inketzt, moet voor elke keuzelijst een keuze maken. Zet | tussen de opties en gebruik && voor meerdere keuzelijsten.</p>
				</div>

				<TextInput
					name="choices"
					hint="Keuzelijst(en)"
					v-model="event.choices">
				</TextInput>
			</template>

			<Toggle
				name="canEdit"
				:question="'Leden mogen ' + (event.hasChoice === 'keuzelijst' ? 'keuze' : 'opmerking') + ' bewerken'"
				v-model="event.canEdit">
			</Toggle>

			<div v-if="event.canEdit" class="subOptions">
				<Toggle
					name="editEnd"
					question="Bewerken niet toestaan na een bepaald moment"
					@input="prepareEditEnd()"
					v-model="event.editEnd">
				</Toggle>

				<div class="moment" v-if="event.editEnd">
					<div class="input-half">
						<label>Bewerken toestaan tot</label>
						<DateInput ref="editEndMoment" name="editEnd" v-model="event.editEndMoment"></DateInput>
					</div>

					<div class="input-half">
						<TextInput
							name="editEndMomentTime"
							mask="HH:MM[:ss]"
							mask-placeholder="__:__[:__]"
							hint="Tijd"
							v-model="event.editEndMomentTime"
							:error="validTime(event.editEndMomentTime) ? null : 'Ongeldige tijd'">
						</TextInput>
					</div>
				</div>
			</div>
		</Stap>

		<Stap
			title="Laatste check"
			:step="10"
			:show-done="true"
			v-on:done="alert('Joepie')">

			<p class="helptext">Controleer de gegevens van je ketzer. Scroll omhoog als er iets niet klopt.</p>
			<div class="category-title">Activiteit</div>
			<div class="info-grid">
				<div>Type</div>
				<div>{{ types[event.type] }}</div>

				<div>Titel</div>
				<div v-if="!event.title" class="missing">niet ingevuld</div>
				<div v-else>{{ event.title }}</div>

				<div>Beschrijving</div>
				<div v-if="!event.shortDescription" class="missing">niet ingevuld</div>
				<div v-else class="whitespace">{{ event.shortDescription }}</div>

				<div>Lees meer</div>
				<div class="whitespace">{{ event.readMore }}</div>

				<div>Moment</div>
				<div v-if="!event.calendarData" class="missing">niet ingevuld</div>
				<div v-else-if="event.multipleDays">
					{{ moment(event.calendarData.start).format("dddd D MMMM Y") | capitalize }} {{ event.entireDay ? '' : event.startTime + ' uur' }} tot<br>
					{{ moment(event.calendarData.end).format("dddd D MMMM Y") | capitalize }} {{ event.entireDay ? '' : event.endTime + ' uur' }}
				</div>
				<div v-else>{{ moment(event.calendarData).format("dddd D MMMM Y") | capitalize }} {{ event.entireDay ? '' : event.startTime + ' - ' + event.endTime + ' uur' }}</div>

				<div>Locatie</div>
				<div v-if="!event.location" class="missing">niet ingevuld</div>
				<div v-else>{{ event.location }}</div>
			</div>

			<div class="category-title">Aanmelden</div>
			<div class="info-grid">
				<div>Inketzen</div>
				<div v-if="event.canEnter">
					<i class="fas fa-check"></i> Toegestaan
					<div v-if="event.enterStart && event.enterStartMoment && event.enterStartMomentTime">Van {{ moment(event.enterStartMoment).format("dddd D MMMM Y") | capitalize }} {{ event.enterStartMomentTime }}</div>
					<div v-if="event.enterEnd && event.enterEndMoment && event.enterEndMomentTime">Tot {{ moment(event.enterEndMoment).format("dddd D MMMM Y") | capitalize }} {{ event.enterEndMomentTime }}</div>
				</div>
				<div v-else><i class="fas fa-times"></i> Niet toegestaan</div>

				<div>Uitketzen</div>
				<div v-if="event.canExit">
					<i class="fas fa-check"></i> Toegestaan
					<div v-if="event.exitEnd && event.exitEndMoment && event.exitEndMomentTime">Tot {{ moment(event.exitEndMoment).format("dddd D MMMM Y") }} {{ event.exitEndMomentTime }}</div>
				</div>
				<div v-else><i class="fas fa-times"></i> Niet toegestaan</div>

				<div>Limiet</div>
				<div v-if="event.hasLimit">Maximaal {{event.limit}}</div>
				<div v-else>Geen maximum</div>

				<div>Doelgroep</div>
				<div v-if="event.hasPermission === 'iedereen'">Iedereen</div>
				<div v-else>
					Geselecteerde groep
					<div>{{ event.permission }}</div>
				</div>

				<div>Vraag</div>
				<div v-if="event.hasChoice === 'invulveld'">Vrije opmerking</div>
				<div v-else>
					Keuzelijst(en)
					<div>{{ event.choices }}</div>
				</div>

				<div v-if="event.hasChoice === 'invulveld'">Opmerking aanpassen</div>
				<div v-else>Keuze(s) aanpassen</div>
				<div v-if="event.canEdit">
					<i class="fas fa-check"></i> Toegestaan
					<div v-if="event.editEnd && event.editEndMoment && event.editEndMomentTime">Tot {{ moment(event.editEndMoment).format("dddd D MMMM Y") }} {{ event.editEndMomentTime }}</div>
				</div>
				<div v-else><i class="fas fa-times"></i> Niet toegestaan</div>

			</div>
		</Stap>
	</div>
</template>

<script>
	import moment from 'moment';

	import SelectButtons from '../velden/SelectButtons';
	import TextInput from '../velden/TextInput';
	import Toggle from '../velden/Toggle';
	import Stap from './onderdelen/Stap';
	import DateInput from '../velden/DateInput';
	import RechtenBouwer from '../velden/RechtenBouwer';
	import IcoonKiezer from "../velden/IcoonKiezer";

	import GroepIcoon from '../../../images/ketzertovenaar/groep.svg';
	import GroepSelectIcoon from '../../../images/ketzertovenaar/groep-select.svg';
	import IedereenIcoon from '../../../images/ketzertovenaar/iedereen.svg';
	import IedereenSelectIcoon from '../../../images/ketzertovenaar/iedereen-select.svg';
	import InvulIcoon from '../../../images/ketzertovenaar/invul.svg';
	import InvulSelectIcoon from '../../../images/ketzertovenaar/invul-select.svg';
	import KeuzeIcoon from '../../../images/ketzertovenaar/keuze.svg';
	import KeuzeSelectIcoon from '../../../images/ketzertovenaar/keuze-select.svg';

	export default {
		name: 'KetzerTovenaar',
		components: {IcoonKiezer, SelectButtons, TextInput, Toggle, Stap, DateInput, RechtenBouwer},
		props: {},
		data: () => ({
			moment: moment,
			types: {
				'vereniging': 'Verenigings-activiteit',
				'lustrum': 'Lustrum-activiteit',
				'dies': 'Dies-activiteit',
				'owee': 'OWee-activiteit',
				'sjaarsactie': 'Sjaarsactie',
				'lichting': 'Lichtings-activiteit',
				'verticale': 'Verticale-activiteit',
				'kring': 'Kring-activiteit',
				'huis': 'Huis-activiteit',
				'ondervereniging': 'Onderverenigings-activiteit',
				'ifes': 'Activiteit van IFES',
				'extern': 'Externe activiteit'
			},
			icons: {
				GroepIcoon, GroepSelectIcoon, IedereenIcoon, IedereenSelectIcoon, InvulIcoon, InvulSelectIcoon, KeuzeIcoon, KeuzeSelectIcoon
			},
			event: {
				type: null,
				title: '',
				shortDescription: '',
				readMore: '',
				multipleDays: false,
				calendarData: null,
				entireDay: false,
				startTime: '',
				endTime: '',
				location: '',
				canEnter: true,
				enterStart: false,
				enterStartMoment: null,
				enterStartMomentTime: '',
				enterEnd: false,
				enterEndMoment: null,
				enterEndMomentTime: '',
				canExit: false,
				exitEnd: false,
				exitEndMoment: null,
				exitEndMomentTime: '',
				canEdit: true,
				editEnd: false,
				editEndMoment: null,
				editEndMomentTime: '',
				hasLimit: false,
				limit: null,
				hasPermission: 'iedereen',
				permission: '',
				hasChoice: 'invulveld',
				choices: '',
			},
			step: 1,
		}),
		mounted() {
			if (sessionStorage.hasOwnProperty('ketzerTovenaar')) {
				let stored = JSON.parse(sessionStorage.getItem('ketzerTovenaar'));
				this.event = stored.event;
				this.step = stored.step;
				if (typeof this.event.calendarData === 'string' && this.event.calendarData) {
					this.event.calendarData = new Date(this.event.calendarData);
				}
				if (typeof this.event.enterStartMoment === 'string' && this.event.enterStartMoment) {
					this.event.enterStartMoment = new Date(this.event.enterStartMoment);
				}
				if (typeof this.event.enterEndMoment === 'string' && this.event.enterEndMoment) {
					this.event.enterEndMoment = new Date(this.event.enterEndMoment);
				}
				if (typeof this.event.exitEndMoment === 'string' && this.event.exitEndMoment) {
					this.event.exitEndMoment = new Date(this.event.exitEndMoment);
				}
				if (typeof this.event.editEndMoment === 'string' && this.event.editEndMoment) {
					this.event.editEndMoment = new Date(this.event.editEndMoment);
				}
			}
		},
		computed: {},
		methods: {
			gotoStep(step, autofocus) {
				this.step = Math.max(this.step, step);
				this.autosave();

				// Scroll to next step
				this.$nextTick(function () {
					let nextStep = $('.stap[data-step=' + step + ']');
					let posTop = nextStep.offset().top - 40;

					let $navbar = $('.navbar');
					if ($navbar.is(':visible')) {
						posTop -= $navbar.height();
					}

					$([document.documentElement, document.body]).animate({
						scrollTop: posTop
					}, 500, function () {
						if (autofocus) {
							nextStep.find('input,textarea').first().focus();
						}
					});
				});
			},
			autosave() {
				sessionStorage.setItem('ketzerTovenaar', JSON.stringify({
					step: this.step,
					event: this.event
				}));
			},
			validTime(time) {
				return /^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/.test(time);
			},
			validStartTime(returning) {
				if (!this.validTime(this.event.startTime)) {
					return returning === true ? false : 'Geef een starttijd op';
				}

				if (returning === true) return true;
			},
			validEndTime(returning) {
				if (!this.validTime(this.event.endTime)) {
					return returning === true ? false : 'Geef een eindtijd op';
				}

				if (this.validTime(this.event.startTime) && !this.event.multipleDays && this.event.endTime <= this.event.startTime) {
					return returning === true ? false : 'Eind moet na start liggen';
				}

				if (returning === true) return true;
			},
			validLimit(limit) {
				return limit > 0;
			},
			prepareEnterEnd() {
				if (this.event.enterEnd) {
					const start = this.event.multipleDays ? this.event.calendarData.start : this.event.calendarData;
					if (start) {
						this.event.enterEndMoment = start;
					}
					this.event.enterEndMomentTime = this.event.entireDay ? '00:00' : this.event.startTime;
				}
			},
			prepareExitEnd() {
				if (this.event.exitEnd) {
					const start = this.event.multipleDays ? this.event.calendarData.start : this.event.calendarData;
					if (start) {
						this.event.exitEndMoment = start;
					}
					this.event.exitEndMomentTime = this.event.entireDay ? '00:00' : this.event.startTime;
				}
			},
			prepareEditEnd() {
				if (this.event.editEnd) {
					const start = this.event.multipleDays ? this.event.calendarData.start : this.event.calendarData;
					if (start) {
						this.event.editEndMoment = start;
					}
					this.event.editEndMomentTime = this.event.entireDay ? '00:00' : this.event.startTime;
				}
			},
		},
		filters: {
			capitalize(value) {
				if (!value) return ''
				value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			}
		}
	}
</script>

<style lang="scss">
	.ketzertovenaar {
		font-family: 'Source Sans Pro', sans-serif;
		line-height: 1.4;
		max-width: 600px;
		margin: 0 auto;
		font-size: 0;
	}

	.explain {
		font-size: 17px;
		margin: 30px 0;

		.heading {
			font-weight: 600;
		}

		p {
			font-weight: 300;
		}
	}

	.rechtenbouwer {
		margin-bottom: 30px;
	}

	.vfc-styles-conditional-class .vfc-main-container {
		font-size: 18px;
		font-family: 'Source Sans Pro', sans-serif !important;
	}

	.c-pane-container + .next {
		margin-top: 20px;
	}

	.c-day {
		min-height: 36px !important;

		.c-day-content {
			width: 33px !important;
			height: 33px !important;
			font-size: 16px !important;
		}

		.c-day-background {
			min-width: 33px !important;
			min-height: 33px !important;
			background-color: #29abe2 !important;
		}
	}

	.subOptions {
		padding-left: 20px;
	}

	.input-half {
		width: calc(50% - 20px);
		display: inline-block;
		margin-right: 40px;

		& + .input-half {
			margin-right: 0;
		}
	}

	.moment {
		position: relative;

		label {
			position: absolute;
			background: white;
			left: 9px;
			padding: 0 8px;
			font-size: 14px;
			font-weight: 400;
			color: #cccccc;
			line-height: 18px;
			top: -9px;
			z-index: 1;
		}
	}

	.category-title {
		font-weight: 300;
		font-size: 22px;
		margin-bottom: 10px;
	}

	.helptext {
		font-size: 17px;
	}

	.info-grid {
		display: grid;
		grid-template-columns: max-content 1fr;
		margin-bottom: 22px;

		& > div {
			font-size: 17px;
			padding: 2px 0;

			&:nth-child(2n + 1) {
				font-weight: 600;
				padding-right: 20px;
			}

			&:nth-child(2n) {
				font-weight: 300;
			}

			&.whitespace {
				white-space: pre-wrap;
			}

			&.missing {
				color: #cc0000;
				font-weight: 600;
			}

			div {
				font-size: 13px;
				color: #444444;
			}

			.fa-check {
				color: #2ECC71;
				margin-right: 10px;
			}

			.fa-times {
				color: #CC0000;
				margin-right: 15px;
			}
		}
	}
</style>
