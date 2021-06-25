<template>
	<div class="icoonKiezer">
		<div class="button" v-for="(option,key) in options">
			<input type="radio" :name="name" :id="name + '-' + key" :value="key" v-model="selected" v-on:change="update" />
			<label :for="name + '-' + key" :style="{backgroundImage: 'url(\'' + (selected === key ? option.imageSelected : option.image) + '\')'}">
				<span class="title">{{ option.title }}</span>
				<span class="description">{{ option.description }}</span>
			</label>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'IcoonKiezer',
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

<style scoped lang="scss">
	input {
		display: none;
	}

	.button:last-child {
		margin-bottom: 30px;
	}

	label {
		cursor: pointer;
		display: block;
		border: 1px solid #cccccc;
		border-radius: 3px;
		transition: background-color 0.1s, color 0.1s, border-color 0.1s;
		font-size: 17px;
		padding: 10px 20px 10px 70px;
		background-position: left 16px center;
		background-size: auto 37px;
		background-repeat: no-repeat;

		.title {
			font-weight: 600;
			display: block;
		}

		.description {
			font-weight: 300;
		}
	}

	input:checked + label {
		background-color: #29abe2;
		color: white;
		border-color: #29abe2;
	}
</style>
