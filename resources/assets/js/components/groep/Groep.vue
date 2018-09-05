<template>
	<div class="card">
		<Spinner v-if="loading" class="card-body" bericht="Groep is aan het laden"/>
		<Error v-else-if="error" class="card-body" bericht="Fout bij laden"/>
		<div v-else="" class="card-body row">

			<div class="col">
				<h3>{{groep.naam}}</h3>
				<p v-html="groep.samenvatting"></p>
			</div>
			<Tabs class="col">
				<Tab name="Pasfoto's tonen" icon="fa fa-user">
					<GroepTabPasfoto :leden="groep.leden"/>
				</Tab>
				<Tab name="Lijst tonen" icon="fa fa-align-justify">
					<GroepTabLijst :leden="groep.leden"/>
				</Tab>
				<Tab name="Statistiek tonen" icon="fa fa-pie-chart">
					<GroepTabStatistiek/>
				</Tab>
				<Tab name="Emails tonen" icon="fa fa-envelope">
					<GroepTabEmail :leden="groep.leden"/>
				</Tab>
				<Tab name="Allergie/dieet tonen" icon="fa fa-heartbeat">
					<GroepTabAllergie/>
				</Tab>
			</Tabs>
			<GroepProgress
				:aantalAanmeldingen="groep.leden.length"
				:aanmeldLimiet="groep.aanmeld_limiet"
				:magBewerken="magBewerken"
				:magAanmelden="magAanmelden"
			/>
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-clock-o" title="Bekijk geschiedenis"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-user" title="Pasfoto's tonen"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-align-justify" title="Lijst tonen"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-pie-chart" title="Statistiek tonen"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-envelope" title="E-mails tonen"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item mr-auto">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-heartbeat" title="Allergie/dieet tonen"></i></a>-->
			<!--</li>-->
			<!--<li class="nav-item">-->
			<!--<a class="nav-link" href="#"><i class="fa fa-expand" title="Uitklappen"></i></a>-->
			<!--</li>-->
		</div>
	</div>
</template>
<script>
	import axios from 'axios';
	import Spinner from '../Spinner';
	import Error from '../Error';
	import GroepTabEmail from './GroepTabEmail';
	import GroepTabLijst from './GroepTabLijst';
	import GroepTabPasfoto from './GroepTabPasfoto';
	import GroepTabStatistiek from './GroepTabStatistiek';
	import Tabs from './Tabs';
	import Tab from './Tab';
	import GroepTabAllergie from './GroepTabAllergie';
	import GroepProgress from './GroepProgress';

	export default {
		components: {
			GroepProgress,
			GroepTabAllergie,
			Tab, Tabs, GroepTabStatistiek, GroepTabPasfoto, GroepTabLijst, GroepTabEmail, Spinner, Error,
		},
		props: ['id'],
		data: function () {
			return {
				loading: true,
				error: false,
				greeting: 'Hello',
				groep: {
					naam: "",
					samenvatting: "",
				},
			};
		},
		created: function () {
			this.laadGroep();
		},
		methods: {
			laadGroep: function () {
				let campaigns = [];
				axios.post(`/groepen/activiteiten/${this.id}/json`)
					.then((response) => {
						this.groep = response.data.data;
					})
					.catch((err) => {
						this.error = true;
					})
					.then(() => {
						this.loading = false;
					});
			},
		},
		computed: {
			magAanmelden() {
				return this.groep.aanmelden_vanaf < new Date() && this.groep.aanmelden_tot > new Date();
			},
			magBewerken() {
				return /*$this->groep->getLid(LoginModel::getUid()) && */this.groep.bewerken_tot > new Date();
			}
		}
	};
</script>
<style scoped>

</style>
