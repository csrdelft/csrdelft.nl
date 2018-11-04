<template>
	<div v-if="heeftGestemd"
			 class="row">
		<div class="col-md-4">{{titel}}</div>
		<div class="col-md-6">
			<ProgressBar :progress="progress" :reverse="true"></ProgressBar>
		</div>
		<div class="col-md-2">{{progressText}}</div>
		<div class="col text-muted pt-2" v-html="beschrijving"></div>
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
							 :checked="dataSelected"
							 v-model="dataSelected"
							 @change="$emit('input', $event.target.checked)"
				/>
				<label :for="'PeilingOptie' + id"
							 class="form-check-label">{{ titel }}</label>
			</div>
		</div>
		<div class="col-md-12 pt-2" v-html="beschrijving"></div>
	</div>
</template>

<script>
	import ProgressBar from '../common/ProgressBar';

	export default {
		name: 'PeilingOptie',
		components: {ProgressBar},
		props: {
			id: Number,
			peilingId: Number,
			titel: String,
			beschrijving: String,
			stemmen: Number,
			ingebrachtDoor: String,
			selected: Boolean
		},
		data: () => ({
			dataSelected: false,
		}),
		created() {
			this.dataSelected = this.selected;
		},
		computed: {
			heeftGestemd() {
				return this.$parent.dataHeeftGestemd;
			},
			totaalStemmen() {
				return this.$parent.dataAantalStemmen;
			},
			progress() {
				return (this.stemmen / this.totaalStemmen * 100).toFixed(2);
			},
			progressText() {
				return `${this.progress}% (${this.stemmen})`;
			},
			isDisabled() {
				return !this.selected && !this.$parent.keuzesOver;
			}
		}
	};
</script>

<style scoped>

</style>
