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
				:error="event.title.length === 0 ? 'Vul een titel in' : ''">
			</TextInput>

		</Stap>

		<Stap
			title="Beschrijf je activiteit"
			:step="3"
			:show-done="true"
			v-on:done="gotoStep(4)">

			<TextInput
				name="shortDescription"
				:max-length="250"
				hint="Korte beschrijving"
				:multiple-lines="5"
				v-model="event.shortDescription">
			</TextInput>

			<TextInput
				name="readMore"
				hint="Lees meer"
				:multiple-lines="5"
				v-model="event.readMore">
			</TextInput>

		</Stap>

	</div>
</template>

<script>
	import SelectButtons from './velden/SelectButtons';
	import TextInput from './velden/TextInput';
	import Stap from './onderdelen/Stap';

	export default {
		name: 'KetzerTovenaar',
		components: {SelectButtons, Stap, TextInput},
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
				readMore: ''
			},
			step: 1,
		}),
		created() {
			if (sessionStorage.hasOwnProperty('ketzerTovenaar')) {
				let stored = JSON.parse(sessionStorage.getItem('ketzerTovenaar'));
				this.event = stored.event;
				this.step = stored.step;
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
					}, 1000, function () {
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
			}
		}
	}
</script>

<style scoped>
	.ketzertovenaar {
		font-family: 'Source Sans Pro', sans-serif;
		line-height: 220%;
		max-width: 600px;
		margin: 0 auto;
	}
</style>
