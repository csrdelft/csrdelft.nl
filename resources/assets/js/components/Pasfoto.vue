<template>
	<a :href="profielUrl" :title="lid.naam" class="lidLInk">
		<img :src="pasfotoUrl" :alt="pasfotoAlt" class="pasfoto"/>
	</a>
</template>

<script>
	export default {
		name: "Pasfoto",
		props: ["lid"],
		computed: {
			pasfotoUrl() {
				return `/plaetjes/pasfoto/${this.lid.uid}.jpg`;
			},
			profielUrl() {
				return `/profiel/${this.lid.uid}`;
			},
			pasfotoAlt() {
				return `Pasfoto van ${this.lid.naam}`;
			},
		},
	};
</script>

<style scoped>

</style>
