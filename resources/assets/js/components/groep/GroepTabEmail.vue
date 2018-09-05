<template>
	<div>
		{{this.emails}}
	</div>
</template>

<script>
	export default {
		name: "GroepTabEmail",
		props: ["leden"],
		computed: {
			emails() {
				return this.leden
					.map((groepLid) => groepLid.lid.email)
					.filter((email) => !!email)
					.join("; ")
			}
		}
	};
</script>

<style scoped>

</style>
