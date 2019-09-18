<template>
	<v-date-picker
		v-model="enteredDate"
		v-on:input="update"
		:key="name + '-dateInput'" class="datePicker"
		:input-props='{
			placeholder: "dd-mm-jjjj"
		}'>
	</v-date-picker>
</template>

<script>


	export default {
		name: 'DateInput',
		components: {},
		props: {
			name: {type: String, required: true},
			value: Object|Date,
		},
		data: () => ({
			enteredDate: null,
		}),
		created() {
			this.enteredDate = this.value;
		},
		methods: {
			update() {
				this.$emit('input', this.enteredDate);
			},
		},
		watch: {
			value: function (newValue) {
				this.enteredDate = newValue;
			}
		}
	}
</script>

<style scoped>
	.errorMessage {
		font-size: 14px;
		font-weight: 400;
		color: #e67e22;
		margin-bottom: 12px;
	}

	.textInput {
		position: relative;
		margin-bottom: 20px;
	}

	.textInput:last-child {
		margin-bottom: 40px;
	}

	label {
		position: absolute;
		background: white;
		left: 9px;
		padding: 0 8px;
		font-size: 14px;
		font-weight: 400;
		color: #cccccc;
		line-height: 18px;
		top: -9px;
	}

	.datePicker >>> input {
		border: 1px solid #cccccc;
		border-radius: 3px;
		padding: 10px 15px;
		font-size: 22px;
		font-weight: 400;
		width: 100%;
		display: block;
		-webkit-appearance: none;
		-moz-appearance: none;
	}

	textarea {
		font-size: 17px;
	}

	input:focus, textarea:focus {
		outline: none;
	}
</style>
