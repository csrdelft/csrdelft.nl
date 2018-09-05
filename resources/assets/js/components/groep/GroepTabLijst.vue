<template>
<div>
	<div v-for="groepLid in leden" :key="groepLid.uid" class="row">
		<div class="col">{{ groepLid.lid.naam }}</div>
		<div class="col">{{ groepLid.opmerking}}</div>
	</div>
</div>
</template>

<script>
	export default {
		name: "GroepTabLijst",
		props: ["leden"],
	};
</script>

<style scoped>
	.row {
		padding: 0.5em;
	}

	.row:nth-child(2n) {
		background-color: #f5f5f5;
	}
</style>
