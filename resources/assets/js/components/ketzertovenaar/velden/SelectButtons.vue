<template>
	<div class="select-buttons">
		<div class="button" v-for="(option,key) in options">
			<input type="radio" :name="name" :id="name + '-' + key" :value="key" v-model="selected" v-on:change="update" />
			<label :for="name + '-' + key">{{ option }}</label>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'SelectButtons',
		components: {},
		props: {
			name: {
				type: String,
				required: true
			},
			options: {
				type: Object,
				required: true,
			},
			value: {
				type: String,
			},
		},
		data: () => ({
			selected: ''
		}),
		created() {
			this.selected = this.value;
		},
		computed: {},
		methods: {
			update() {
				this.$emit('input', this.selected);
			},
		}
	}
</script>

<style scoped>
	.select-buttons {
		font-size: 0;
		display: grid;
		grid-template-columns: 50% 50%;
		grid-column-gap: 10px;
		grid-row-gap: 7px;
	}

	@media screen and (max-width: 400px) {
		.select-buttons {
			grid-template-columns: 100%;
		}
	}

	.button {
	}

	input {
		display: none;
	}

	label {
		display: block;
		text-align: center;
		font-size: 18px;
		font-weight: 300;
		padding: 4px;
		border: 1px solid #cccccc;
		border-radius: 3px;
		margin: 0;
	}

	input:checked + label {
		background: #29abe2;
		color: white;
		border-color: #29abe2;
	}
</style>
