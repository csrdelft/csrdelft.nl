<template>
	<div v-if="!kanStemmen"
			 class="row">
		<div class="col-md-4">{{titel}}</div>
		<div class="col-md-6">
			<ProgressBar :progress="progress" :reverse="true"></ProgressBar>
		</div>
		<div class="col-md-2">{{progressText}}</div>
		<div ref="beschrijving_gestemd" class="col text-muted pt-2" v-html="beschrijving"></div>
	</div>
	<div v-else=""
			 class="row">
		<div class="col-md-12">
			<div class="form-check">
				<input type="checkbox"
							 class="form-check-input"
							 name="optie"
							 :value="id"
							 :id="'PeilingOptie' + id"
							 :disabled="isDisabled"
							 :checked="selected"
							 @change="$emit('input', $event.target.checked)"/>
				<label :for="'PeilingOptie' + id"
							 class="form-check-label">{{ titel }}</label>
			</div>
		</div>
		<div ref="beschrijving" class="col-md-12 pt-2" v-html="beschrijving"></div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component, Prop, Watch} from 'vue-property-decorator';
	import initContext from '../../context';
	import ProgressBar from '../common/ProgressBar';

	@Component({
		components: {ProgressBar},
	})
	export default class PeilingOptie extends Vue {
		@Prop()
		private id: string;
		@Prop()
		private peilingId: number;
		@Prop()
		private titel: string;
		@Prop()
		private beschrijving: string;
		@Prop()
		private stemmen: number;
		@Prop()
		private magStemmen: boolean;
		@Prop()
		private aantalGestemd: number;
		@Prop()
		private heeftGestemd: boolean;
		@Prop()
		private keuzesOver: boolean;
		@Prop()
		private selected: boolean;

		protected mounted() {
			this.initBeschrijvingContext();
		}

		@Watch('kanStemmen')
		protected initBeschrijvingContext() {
			setTimeout(() => {
				if (this.kanStemmen) {
					initContext(this.$refs.beschrijving as Node);
				} else {
					initContext(this.$refs.beschrijving_gestemd as Node);
				}
			});
		}

		protected get kanStemmen() {
			return this.magStemmen && !this.heeftGestemd;
		}

		protected get progress() {
			return (this.stemmen / this.aantalGestemd * 100).toFixed(2);
		}

		protected get progressText() {
			return `${this.progress}% (${this.stemmen})`;
		}

		protected get isDisabled() {
			return !this.selected && !this.keuzesOver;
		}
	}
</script>

<style scoped>

</style>
