<template>
	<div class="field">
		<div v-if="error && error !== true && validating" class="errorMessage">{{ error }}</div>
		<div class="textInput">
			<label :for="name" v-if="hint">{{ hint }}</label>

			<input
				:type="number ? 'number' : 'text'"
				min="0"
				:name="name"
				:id="name"
				:maxlength="maxLength ? maxLength : ''"
				v-model="enteredText"
				v-if="!multipleLines"
				v-on:keyup="update"
				v-on:blur="validate"
				@keyup.enter="$emit('next')"
				ref="inputField"
				autocomplete="off" />

			<textarea
				:name="name"
				:id="name"
				:rows="multipleLines"
				:maxlength="maxLength ? maxLength : ''"
				v-if="multipleLines"
				v-model="enteredText"
				v-on:keyup="update"
				v-on:blur="validate">
			</textarea>

			<div class="lengthCounter" v-if="maxLength">{{ remainingLength }}/{{ maxLength }}</div>
		</div>
	</div>
</template>

<script>
	import InputMask from "inputmask/dist/inputmask/inputmask.date.extensions";

	export default {
		name: 'TextInput',
		components: {},
		props: {
			name: {type: String, required: true},
			value: String,
			hint: String,
			maxLength: Number,
			multipleLines: Number,
			error: String,
			mask: String,
			maskPlaceholder: String,
			number: {type: Boolean, required: false}
		},
		data: () => ({
			enteredText: '',
			validating: false,
		}),
		created() {
			this.enteredText = this.value;
		},
		mounted() {
			if (this.mask) {
				let im = new InputMask({"alias": "datetime", inputFormat: this.mask, placeholder: this.maskPlaceholder});
				im.mask(this.$refs.inputField);
			}
		},
		computed: {
			remainingLength() {
				return this.enteredText.length;
			},
		},
		methods: {
			update() {
				this.$emit('input', this.enteredText);
			},
			validate() {
				this.validating = true;
			}
		},
		watch: {
			value: function (newValue) {
				this.enteredText = newValue;
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

	input, textarea {
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

	.lengthCounter {
		font-size: 15px;
		font-weight: 600;
		color: #b3b3b3;
		text-align: right;
		line-height: 24px;
	}
</style>
