<template>
	<div>
		<div class="kopje">{{ $parent.opties[index].groep }}</div>
		<div class="opties" v-for="(details, key) in $parent.opties[index].opties">
			<div class="selecteer">
				<toggle-button
					:width="40"
					:value="$parent.opties[index].opties[key].actief"
					@change="toggle($event, key)" :sync="true"/>
			</div>
			<div class="uitleg"
				 @click="toggle({value: !$parent.opties[index].opties[key].actief}, key)">
				{{ $parent.opties[index].opties[key].optie }}
				<span>(&euro; {{ $parent.opties[index].opties[key].prijs.toFixed(2).replace('.', ',') }})</span>
			</div>
		</div>
	</div>
</template>
<script lang="ts">
	import Vue from 'vue';
	import {ToggleButton} from 'vue-js-toggle-button';
	import {Component, Prop} from 'vue-property-decorator';

	@Component({
		components: {ToggleButton},
	})
	export default class OptieWeergave extends Vue {
		@Prop()
		protected index: number;

		protected toggle(value: any, key: string) {
			if (this.$parent.laden) {
				return;
			}
			const details = this.$parent.opties[this.index].opties[key];
			details.actief = value.value;
			if ('pre' in details && value.value) {
				this.$parent.opties[this.index].opties[details.pre].actief = true;
			}
			if ('post' in details && !value.value) {
				this.$parent.opties[this.index].opties[details.post].actief = false;
			}
			this.$parent.gewijzigd = true;
			this.$parent.berekenTotaal();
			this.$forceUpdate();
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
