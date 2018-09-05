<template>
	<div class="badge badge-red">
		{{bericht}}
	</div>
</template>

<script>
	export default {
		props: ["bericht"],
		name: "Error",
	};
</script>

<style scoped>

</style>
