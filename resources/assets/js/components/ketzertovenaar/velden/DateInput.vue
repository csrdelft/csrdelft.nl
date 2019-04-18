<template>
	<functional-calendar
		:is-modal="true"
		:change-month-function="true"
		:change-year-function="true"
		:is-date-picker="true"
		:key="name + '-dateInput'"
		v-model="enteredDate"
		v-on:input="update"
		:date-format="'dd-mm-yyyy'"
		:day-names="['Zo','Ma','Di','Wo','Do','Vr','Za']"
		:month-names="['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December']"
		placeholder="dd-mm-jjjj">
	</functional-calendar>
</template>

<script>
	import FunctionalCalendar from 'vue-functional-calendar';

	export default {
		name: 'DateInput',
		components: {FunctionalCalendar},
		props: {
			name: {type: String, required: true},
			value: Object,
		},
		data: () => ({
			enteredDate: null,
		}),
		created() {
			this.enteredDate = this.value;
		},
		methods: {
			update() {
				this.$emit('input', this.enteredDate);
			},
		},
		watch: {
			value: function (newValue) {
				this.enteredDate = newValue;
			}
		}
	}
</script>

<style>
	.moment .vfc-styles-conditional-class input.vfc-single-input {
		border: 1px solid #cccccc;
		border-radius: 3px;
		padding: 10px 15px;
		font-size: 22px;
		font-weight: 400;
		width: 100%;
		display: block;
		-webkit-appearance: none;
		-moz-appearance: none;
		color: black;
		text-align: left;
	}

	.vfc-styles-conditional-class .vfc-main-container.vfc-modal {
		width: 400px !important;
    max-width: calc(100% - 50px);
	}
</style>
