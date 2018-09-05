<template>
	<div>
		<Pasfoto v-for="groepLid in leden" :lid="groepLid.lid" :key="groepLid.uid"/>
	</div>
</template>

<script>
	import Pasfoto from '../Pasfoto';
	export default {
		name: "GroepTabPasfoto",
		components: {Pasfoto},
		props: ["leden"],

	};
</script>

<style scoped>

</style>
