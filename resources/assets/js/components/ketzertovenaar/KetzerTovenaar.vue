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
			:show-done="true"
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
		</Stap>

		<Stap
			title="Wie mag er komen?"
			:step="8"
			:show-done="true"
			v-on:done="gotoStep(9)">
		</Stap>

		<Stap
			title="Wat wil je weten?"
			:step="9"
			:show-done="true"
			v-on:done="gotoStep(10)">
		</Stap>

		<Stap
			title="Laatste check"
			:step="10"
			v-on:done="alert('Joepie')">
		</Stap>
	</div>
</template>

<script>
	import SelectButtons from '../velden/SelectButtons';
	import TextInput from '../velden/TextInput';
	import Toggle from '../velden/Toggle';
	import Stap from './onderdelen/Stap';
	import DateInput from '../velden/DateInput';

	export default {
		name: 'KetzerTovenaar',
		components: {SelectButtons, TextInput, Toggle, Stap, DateInput},
		props: {},
		data: () => ({
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
				hasLimit: false,
				limit: null,
				hasPermission: false,
				permission: '',
				hasChoice: false,
				choices: '',
			},
			step: 1,
		}),
		mounted() {
			if (sessionStorage.hasOwnProperty('ketzerTovenaar')) {
				let stored = JSON.parse(sessionStorage.getItem('ketzerTovenaar'));
				this.event = stored.event;
				this.step = stored.step;
				if (typeof this.event.calendarData === 'string') {
					this.event.calendarData = new Date(this.event.calendarData);
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
			prepareEnterEnd() {
				if (this.event.enterEnd) {
					const start = this.event.multipleDays ? this.event.calendarData.start : this.event.calendarData;
					if (start) {
						this.event.enterEndMoment = start;
					}
					this.event.enterEndMomentTime = this.event.startTime;
				}
			},
			prepareExitEnd() {
				if (this.event.exitEnd) {
					const start = this.event.multipleDays ? this.event.calendarData.start : this.event.calendarData;
					if (start) {
						this.event.exitEndMoment = start;
					}
					this.event.exitEndMomentTime = this.event.startTime;
				}
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
</style>
