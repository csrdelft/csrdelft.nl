<template>
	<div>
		<a @click="toevoegen">
			<span :class="icon"></span> {{text}}
		</a>
	</div>
</template>

<script>
	import axios from 'axios';
	import {domUpdate} from '../../context';

	export default {
		name: 'PeilingOptieToevoegen',
		data: () => ({
			icon: 'ico add',
			text: 'Optie toevoegen'
		}),
		computed: {
			optieToevoegenUrl() {
				return `/peilingen/opties/${this.$parent.id}/toevoegen`;
			}
		},
		methods: {
			toevoegen(event) {
				event.preventDefault();
				this.icon = 'ico arrow_rotate_clockwise rotating';
				axios.post(this.optieToevoegenUrl.toString(), null, AXIOS_LOCAL_CSRF_CONF)
					.then((response) => {
						domUpdate(response.data);
						this.icon = 'ico add';
					})
					.catch(() => {
						this.icon = 'ico cancel';
						this.text = 'Mag geen optie meer toevoegen';
					});
			},
		}
	};
</script>

<style scoped>

</style>
