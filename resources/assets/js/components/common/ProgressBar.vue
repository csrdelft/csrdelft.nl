<template>
	<div :class="{'progress': true, 'flex-row-reverse': reverse}">
		<div class="progress-bar" :style="{width: progress + '%'}">
			<slot/>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'ProgressBar',
		props: {
			progress: {
				type: Number
			},
			reverse: {
				type: Boolean,
				required: false,
				default: () => false,
			}
		},
	}
</script>

<style scoped>

</style>
