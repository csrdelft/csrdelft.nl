<template>
	<span :class="iconClass"></span>
</template>

<script>
	export default {
		name: 'Icon',
		props: ['icon'],
		computed: {
			iconClass() {
				return `ico ${this.icon}`;
			}
		}
	}
</script>

<style scoped>

</style>
