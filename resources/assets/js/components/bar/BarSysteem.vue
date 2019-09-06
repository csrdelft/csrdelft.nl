<template>
	<div>
		<nav>
			<Clock/>
		</nav>
		<main>
			<ButtonKeyboard v-on:input-change="filterList"/>
		</main>
	</div>
</template>
<script lang="ts">
	import Vue from 'vue';
	import {Component} from 'vue-property-decorator';
	import ButtonKeyboard from './ButtonKeyboard.vue';
	import Clock from './Clock.vue';

	@Component({
		components: {ButtonKeyboard, Clock},
	})
	export default class BarSysteem extends Vue {
		private filterList(searchString: string) {
			// TODO Implement filtering people list
		}
	}
</script>
<style scoped>
	main {
		padding: 0 5px;
	}
</style>
