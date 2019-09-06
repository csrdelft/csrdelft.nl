<template>
	<div id="root">
		<b-input-group size="lg">
			<b-form-input id="persoon-input" placeholder="Naam" type="text" v-model="currentText" v-on:keyup="$emit('input-change', currentText)"/>
			<b-input-group-append>
				<b-button v-on:click="showKeyboard=!showKeyboard"><span class="far fa-keyboard"/></b-button>
			</b-input-group-append>
		</b-input-group>
		<div id="button-container" v-if="showKeyboard">
			<ul id="keyboard">
				<li :class="button.class" v-on:click="keyPress(button.display)" v-for="button in layout">{{button.display}}</li>
			</ul>
		</div>
	</div>
</template>
<script lang="ts">
	import Vue from 'vue';
	import {Component} from 'vue-property-decorator';

	@Component
	export default class ButtonKeyboard extends Vue {
		private static layoutToButtons(layout: string[]) {
			return layout.map((key) => {
				switch (key) {
					case 'delete':
					case 'space':
					case 'leeg':
						return {
							display: key,
							class: key,
						};
					case '\n':
						return {
							display: '',
							class: 'spacer clear',
						};
					case '\t':
						return {
							display: '',
							class: 'spacer',
						};
					default:
						return {
							display: key,
							class: 'letter',
						};
				}
			});
		}

		public currentText = '';
		private showKeyboard = true;
		private layout = ButtonKeyboard.layoutToButtons([
			'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'delete', '\n',
			'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'leeg', '\n',
			'\t', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', 'space',
		]);

		private keyPress(key: string) {
			switch (key) {
				case 'delete':
					this.currentText = this.currentText.slice(0, -1);
					break;
				case'leeg':
					this.currentText = '';
					break;
				case 'space':
					this.currentText += ' ';
					break;
				case '':
					return;
				default:
					this.currentText += key.toLowerCase();
					break;
			}
			this.$emit('input-change', this.currentText);
		}
	}
</script>
<style scoped>
	#root {
		height: 325px;
		width: 100%;
	}

	div {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		font-size: 14px;
		line-height: 1.42857143;
	}

	#button-container {
		margin: 10px auto;
		width: 781px;
	}

	#keyboard {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	#keyboard li {
		float: left;
		margin: 0 5px 5px 0;
		width: 60px;
		height: 60px;
		line-height: 60px;
		font-size: 30px;
		text-align: center;
		border: 1px solid #f9f9f9;
		background: #d0cfd1;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}

	#keyboard .spacer {
		width: 32px;
		margin-right: 0;
		background: none;
		position: relative;
		left: -10000px;
	}

	#keyboard .clear {
		clear: left;
	}

	#keyboard .delete, #keyboard .leeg, #keyboard .space {
		width: 126px;
	}

	#keyboard li:hover {
		position: relative;
		top: 1px;
		left: 1px;
		border-color: #e5e5e5;
		cursor: pointer;
	}

	span.fa-keyboard {
		font-size: 40px;
	}

	#persoon-input {
		font-size: 40px;
		height: 60px;
	}
</style>
