<template>
	<div class="selectButtons">
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
			name: {type: String, required: true},
			options: {type: Object, required: true},
			value: String,
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
				this.$emit('done');
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
	.selectButtons {
		font-size: 0;
		display: grid;
		grid-template-columns: 1fr 1fr;
		grid-column-gap: 10px;
		grid-row-gap: 7px;
		margin-bottom: 20px;
	}

	.selectButtons:last-child {
		margin-bottom: 40px;
	}

	@media screen and (max-width: 400px) {
		.selectButtons {
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
		transition: background-color 0.1s, color 0.1s, border-color 0.1s;
	}

	input:checked + label, label:hover {
		background: #29abe2;
		color: white;
		border-color: #29abe2;
	}
</style>
