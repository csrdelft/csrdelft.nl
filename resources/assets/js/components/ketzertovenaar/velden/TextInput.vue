<template>
	<div class="field">
		<div v-if="error && validating" class="errorMessage">{{ error }}</div>
		<div class="textInput">
			<label :for="name" v-if="hint">{{ hint }}</label>

			<input
				type="text"
				:name="name"
				:id="name"
				:maxlength="maxLength ? maxLength : ''"
				v-model="enteredText"
				v-if="!multipleLines"
				v-on:input="update"
				v-on:blur="validate"
				@keyup.enter="$emit('next')" />

			<textarea
				:name="name"
				:id="name"
				:rows="multipleLines"
				:maxlength="maxLength ? maxLength : ''"
				v-if="multipleLines"
				v-model="enteredText"
				v-on:input="update"
				v-on:blur="validate">
			</textarea>

			<div class="lengthCounter" v-if="maxLength">{{ remainingLength }}/{{ maxLength }}</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'TextInput',
		components: {},
		props: {
			name: {type: String, required: true},
			value: String,
			hint: String,
			maxLength: Number,
			multipleLines: Number,
			error: String
		},
		data: () => ({
			enteredText: '',
			validating: false,
		}),
		created() {
			this.enteredText = this.value;
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
