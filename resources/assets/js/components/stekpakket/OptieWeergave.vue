<template>
	<div>
		<div class="kopje">{{ value.groep }}</div>
		<div class="opties" v-for="(details, key) in value.opties">
			<div class="selecteer">
				<toggle-button :width="40" v-model="details.actief" @change="toggle"/>
			</div>
			<div class="uitleg">{{ details.optie }} <span>(&euro; {{ details.prijs.toFixed(2).replace('.', ',') }})</span></div>
		</div>
	</div>
</template>
<script lang="ts">
	import Vue from 'vue';
	import {ToggleButton} from 'vue-js-toggle-button';
	import {Component, Prop, Watch} from 'vue-property-decorator';
	import {OptieGroep} from './StekPakket.vue';

	@Component({
		components: {ToggleButton},
	})
	export default class OptieWeergave extends Vue {
		@Prop()
		protected value: OptieGroep;

		protected toggle() {
			this.$emit('input', this.value);
		}
	}
</script>
<style scoped lang="scss">
	.kopje {
		font-size: 13pt;
		font-weight: 600;
		padding-bottom: 5px;
		padding-top: 4px;
	}

	.opties {
		display: grid;
		grid-template-columns: min-content auto;
		padding-bottom: 6.6px;

		.selecteer {
			padding-right: 16px;
		}

		.uitleg {
			font-weight: 300;
			font-size: 12pt;
			line-height: 22px;
			cursor: pointer;

			span {
				color: #9C9C9C;
				display: inline-block;
			}
		}
	}
</style>
