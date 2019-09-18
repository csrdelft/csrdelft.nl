<template>
	<div class="toggleButton">
		<div class="question">{{ question }}</div>
		<div class="switch">
			<toggle-button
				:name="this.name"
				:value="this.selected"
				:sync="true"
				color="#2ecc71"
				v-on:change="$emit('input', $event.value)">
			</toggle-button>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'Toggle',
		components: {},
		props: {
			name: {type: String, required: true},
			value: Boolean,
			question: {type: String, required: true}
		},
		data: () => ({
			selected: null
		}),
		created() {
			this.selected = this.value;
		},
		computed: {},
		methods: {
			update() {
				this.$emit('input', this.selected);
			},
		},
		watch: {
			value: function (newValue) {
				this.selected = newValue;
			}
		}
	}
</script>

<style scoped>
	.toggleButton {
		display: grid;
		grid-template-columns: 1fr 50px;
		grid-column-gap: 10px;
		align-items: center;
		margin-bottom: 20px;
	}

	.toggleButton:last-child {
		margin-bottom: 40px;
	}

	.toggleButton .question {
		font-size: 17px;
		font-weight: 400;
		color: black;
	}
</style>
