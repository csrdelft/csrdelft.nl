<template>
	<div class="stap" v-if="$parent.step >= step" :data-step="step">
		<div class="title" v-if="title">{{ title }}</div>
		<slot></slot>

		<div v-if="showDone && $parent.step === step" class="next" v-on:click="$emit('done')">Volgende</div>
	</div>
</template>

<script>
	export default {
		name: 'Stap',
		components: {},
		props: {
			title: String,
			step: {type: Number, required: true},
			showDone: Boolean,
		},
		data: () => ({}),
		created() {
		},
		computed: {},
		methods: {}
	}
</script>

<style scoped>
	.stap {
		margin-bottom: 40px;
	}

	.stap:last-of-type {
		min-height: calc(100vh - 40px);
	}

	.title {
		font-weight: 600;
		font-size: 22px;
		text-align: center;
		margin-bottom: 25px;
	}

	.next {
		background: #2ecc71;
		border-radius: 3px;
		color: white;
		font-size: 19px;
		font-weight: bold;
		padding: 7px 10px;
		cursor: pointer;
		text-align: center;
	}

	.next:hover {
		background: #34e27a;
	}
</style>
