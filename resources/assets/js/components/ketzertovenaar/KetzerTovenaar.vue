<template>
	<div class="ketzertovenaar">
		<div class="step">
			<SelectButtons name="type" :options="types" v-model="formData.type"></SelectButtons>
		</div>
	</div>
</template>

<script>
	import SelectButtons from './velden/SelectButtons';

	export default {
		name: 'KetzerTovenaar',
		components: {SelectButtons},
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
			formData: {
				type: 'lichting',
			}
		}),
		created() {},
		computed: {},
		methods: {}
	}
</script>

<style scoped>
	.ketzertovenaar {
		font-family: 'Source Sans Pro', sans-serif;
		max-width: 600px;
		margin: 0 auto;
	}
</style>
