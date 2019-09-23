<template>
	<div class="rechtenbouwer">
		<div class="manual" v-if="manual">
			<input type="text" :name="fieldName" v-model="string" @input="update()" autocomplete="off">
			<div class="actions">
				<span @click="gotoAutomatic()" class="pull-right">simpel</span>
			</div>
		</div>
		<div class="automatic" v-else>
			<input type="hidden" :name="fieldName" v-model="string">
			<div class="criterium" v-for="(criterium, cIndex) in structure">
				<div class="rule" v-for="(rule, rIndex) in criterium">
					<div v-if="rIndex > 0" class="divider" :class="{'divider-en': rule.type === 'en'}">
						<span>{{ rule.type }}</span>
					</div>
					<input type="text" v-model="rule.value" @input="update()" autocomplete="off">
					<div class="buttons">
						<span v-if="structure[cIndex].length > 1 || structure.length > 1" @click="removeRule(cIndex, rIndex)">x</span>
						<span @click="addRule(cIndex, rIndex, 'of')">OF</span>
						<span @click="addRule(cIndex, rIndex, 'en')">EN</span>
					</div>
				</div>
			</div>
			<div class="actions">
				<span @click="addCriterium()">criterium toevoegen</span>
				<span @click="gotoManual()" class="pull-right">geavanceerd</span>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'RechtenBouwer',
		components: {},
		props: {
			value: {type: String},
			name: {type: String},
		},
		data: () => ({
			structure: [
				[
					{type: 'en', value: 'lichting:2018'},
					{type: 'of', value: 'bestuur:ht'},
					{type: 'en', value: 'geslacht:m'},
				],
				[
					{type: 'en', value: 'comisssie:soccie'}
				],
			],
			string: '',
			manual: true,
			fieldName: 'rechten',
		}),
		computed: {},
		methods: {
			update() {
				if (!this.manual) {
					this.convertToString();
				}
				this.$emit('input', this.string);
			},
			convertToString() {
				this.string = this.structure.map((part) => {
					return part.map((comp, index) => {
						let ret = '';
						if (index !== 0) {
							ret += comp.type === 'en' ? '+' : '|';
						}
						ret += comp.value;
						return ret;
					}).join('');
				}).join(',');
			},
			convertToStructure() {
				let convert = this.string;
				convert = convert.replace(' ', '');
				let groups = convert.split(',');
				this.structure = groups.map((group) => {
					let remaining = group;
					let found = [];
					let nextSign = 'en';
					do {
						const indexAnd = remaining.indexOf('+');
						const indexOr = remaining.indexOf('|');
						const firstIndex = indexAnd === -1 || indexOr === -1 ? Math.max(indexAnd, indexOr) : Math.min(indexAnd, indexOr);
						if (firstIndex >= 0) {
							found.push(
								{type: nextSign, value: remaining.substr(0, firstIndex)}
							);
							remaining = remaining.substr(firstIndex + 1);
							nextSign = indexAnd > -1 && indexAnd < indexOr ? 'en' : 'of';
						} else {
							found.push(
								{type: nextSign, value: remaining}
							);
							remaining = '';
						}
					} while (remaining.length > 0);
					return found;
				});
			},
			gotoManual() {
				this.manual = true;
			},
			gotoAutomatic() {
				this.convertToStructure();
				this.manual = false;
			},
			removeRule(cIndex, rIndex) {
				this.structure[cIndex].splice(rIndex, 1);
				if (this.structure[cIndex].length === 0) {
					if (this.structure.length > 1) {
						this.structure.splice(cIndex, 1);
					} else {
						this.addRule(cIndex, 0, 'en');
					}
				}
				this.update();
			},
			addRule(cIndex, rIndex, type) {
				this.structure[cIndex].splice(rIndex + 1, 0, {type: type, value: ''});
				this.update();
			},
			addCriterium() {
				this.structure.push([{type: 'en', value: ''}]);
				this.update();
			},
		},
		created() {
			if (this.value) {
				this.string = this.value;
			}
			if (this.name) {
				this.fieldName = this.name;
			}
			this.gotoAutomatic();
		}
	}
</script>

<style lang="scss">
	.rechtenbouwer {
		font-size: 16px;
	}
</style>
