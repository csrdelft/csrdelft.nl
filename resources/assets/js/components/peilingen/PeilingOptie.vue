<template>
	<div v-if="heeft_gestemd"
			 class="row">
		<div class="col-md-4">{{titel}}</div>
		<div class="col-md-6">
			<ProgressBar :progress="progress" :reverse="true"></ProgressBar>
		</div>
		<div class="col-md-2">{{progressText}}</div>
		<div class="col text-muted">{{beschrijving}}</div>
	</div>
	<div v-else=""
			 class="form-check">
		<input type="checkbox"
					 class="form-check-input"
					 name="optie"
					 :value="id"
					 :id="'PeilingOptie' + id"
					 :disabled="isDisabled"
					 v-model="selected"
					 @change="$emit('input', $event.target)"
					 />
		<label :for="'PeilingOptie' + id"
					 class="form-check-label">{{ titel }}</label>
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
		},
		data: () => ({
			selected: false,
		}),
		computed: {
			heeft_gestemd() {
				return this.$parent.heeftGestemd;
			},
			totaalStemmen() {
				return this.$parent.aantalStemmen;
			},
			progress() {
				return this.stemmen / this.totaalStemmen * 100;
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
