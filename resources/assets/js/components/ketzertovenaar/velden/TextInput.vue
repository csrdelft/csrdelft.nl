<template>
	<div class="text-input">
		<label :for="name" v-if="hint">{{ hint }}</label>
		<input v-if="!multipleLines" :id="name" type="text" :name="name" v-model="enteredText" v-on:input="update" :maxlength="maxLength ? maxLength : ''"/>
		<textarea v-if="multipleLines" :rows="multipleLines" :id="name" :name="name" v-model="enteredText" v-on:input="update" :maxlength="maxLength ? maxLength : ''"></textarea>
		<div class="lengthCounter" v-if="maxLength">{{ remainingLength }}/{{ maxLength }}</div>
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
		},
		data: () => ({
			enteredText: ''
		}),
		created() {
			this.text = this.value;
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
		}
	}
</script>

<style scoped>
	.text-input {
		position: relative;
		margin-bottom: 20px;
	}

	.text-input:last-child {
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
