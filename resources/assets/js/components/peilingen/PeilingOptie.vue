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
							 @change="$emit('input', $event.target.checked)" />
				<label :for="'PeilingOptie' + id"
							 class="form-check-label">{{ titel }}</label>
			</div>
		</div>
		<div ref="beschrijving" class="col-md-12 pt-2" v-html="beschrijving"></div>
	</div>
</template>

<script>
	import ProgressBar from '../common/ProgressBar';
	import {init} from "../../ctx";

	export default {
		name: 'PeilingOptie',
		components: {ProgressBar},
		props: {
			id: Number,
			peilingId: Number,
			titel: String,
			beschrijving: String,
			stemmen: Number,
			magStemmen: Boolean,
			aantalGestemd: Number,
			heeftGestemd: Boolean,
			keuzesOver: Boolean,
			selected: Boolean
		},
		mounted() {
			this.initBeschrijvingContext();

			this.$watch('kanStemmen', () => this.initBeschrijvingContext());
		},
		methods: {
			initBeschrijvingContext() {
				if (this.kanStemmen) {
					init(this.$refs.beschrijving);
				} else {
					init(this.$refs.beschrijving_gestemd);
				}
			}
		},
		computed: {
			kanStemmen() {
				return this.magStemmen && !this.heeftGestemd;
			},
			progress() {
				return (this.stemmen / this.aantalGestemd * 100).toFixed(2);
			},
			progressText() {
				return `${this.progress}% (${this.stemmen})`;
			},
			isDisabled() {
				return !this.selected && !this.keuzesOver;
			}
		}
	};
</script>

<style scoped>

</style>
