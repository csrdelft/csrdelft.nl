<template>
	<div v-if="aanmeldLimiet !== null" class="progress" :title="vooruitgangBeschrijving">
		<div
			:class="{'progress-bar': true, [status]: true}"
			role="progressbar"
			:aria-valuenow="vooruitgang"
			aria-valuemin="0"
			aria-valuemax="100"
			:style="{width: vooruitgangProcent}">
			{{vooruitgangProcent}}
		</div>
	</div>
</template>

<script>
	export default {
		name: "GroepProgress",
		props: ["aantalAanmeldingen", "aanmeldLimiet", "magBewerken", "magAanmelden"],
		computed: {
			vooruitgang() {
				return this.aantalAanmeldingen / this.aanmeldLimiet * 100;
			},
			vooruitgangProcent() {
				return this.vooruitgang.toFixed(0) + "%";
			},
			vooruitgangBeschrijving() {
				console.log(this.aanmeldLimiet);
				if (this.magAanmelden) {
					let verschil = this.aanmeldLimiet - this.aantalAanmeldingen;
					if (verschil === 0) {
						return 'Inschrijvingen vol!';
					} else {
						return 'Inschrijvingen geopend! Nog ' + verschil + ' plek' + (verschil === 1 ? '' : 'ken') + ' vrij.';
					}
				} // Bewerken mogelijk?
				else if (this.magBewerken) {
					return 'Inschrijvingen gesloten! Inschrijving bewerken is nog wel toegestaan.';
				} else {
					return 'Inschrijvingen gesloten!';
				}
			},
			status() {
				if (this.magAanmelden) {
					if (this.legePlekken === 0) {
						return 'progress-bar-info';
					}  else {
						return 'progress-bar-success';
					}
				} else if (this.magBewerken) {
					return 'progress-bar-warning';
				} else {
					return 'progress-bar-info';
				}
			},
			legePlekken() {
				return this.aanmeldLimiet - this.aantalAanmeldingen;
			},
		}
	};
</script>

<style scoped>

</style>
