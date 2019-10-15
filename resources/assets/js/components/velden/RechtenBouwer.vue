<template>
	<div class="rechtenbouwer">
		<div class="manual" v-if="manual">
			<input type="text" :name="fieldName" v-model="string" @input="update()" autocomplete="off">
			<div class="actions clearfix">
				<span @click="gotoAutomatic()" class="float-right"><i class="fa fa-cog"></i> simpel</span>
			</div>
		</div>
		<div class="automatic" v-else>
			<input type="hidden" :name="fieldName" v-model="string">
			<template v-for="(criterium, cIndex) in structure">
				<div v-if="cIndex > 0" class="main-divider">
					<span>of</span>
				</div>
				<div class="criterium">
					<div class="rule" v-for="(rule, rIndex) in criterium">
						<div v-if="rIndex > 0" class="divider" :class="{'divider-en': rule.type === 'en'}">
							<span>{{ rule.type }}</span>
						</div>
						<input type="text" v-model="rule.value" @input="update()" autocomplete="off">
						<div class="buttons">
							<span v-if="structure[cIndex].length > 1 || structure.length > 1" @click="removeRule(cIndex, rIndex)"><i class="fa fa-times"></i></span>
							<span @click="addRule(cIndex, rIndex, 'of')">OF</span>
							<span @click="addRule(cIndex, rIndex, 'en')">EN</span>
						</div>
					</div>
				</div>
			</template>
			<div class="actions clearfix">
				<span @click="addCriterium()"><i class="fa fa-plus"></i> criterium toevoegen</span>
				<span @click="gotoManual()" class="float-right"><i class="fa fa-cog"></i> geavanceerd</span>
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
		font-family: 'Source Sans Pro', sans-serif;

		input {
			font-size: 18px;
			border: 1px solid #cccccc;
			border-radius: 5px;
			display: block;
			padding: 7px 15px;
			font-weight: 300;
			width: 100%;
		}

		.automatic {
			.criterium {
				border: 1px solid #cccccc;
				border-radius: 5px;
				padding: 26px 20px;

				.rule {
					.divider {
						margin: 0 0 15px;
						position: relative;

						span {
							position: relative;
							display: inline-block;
							background: white;
							padding-right: 12px;
							z-index: 1;
							font-weight: 400;
							text-transform: uppercase;
							color: #999999;
							font-size: 16px;
						}

						&:after {
							content: '';
							position: absolute;
							top: 12px;
							width: 100%;
							height: 0;
							border-bottom: 1px solid #cccccc;
							z-index: 0;
							left: 0;
						}

						&.divider-en {
							&:after {
								border-bottom-width: 3px;
								top: 10px;
							}

							span {
								font-weight: 600;
							}
						}
					}

					.buttons {
						text-align: right;
						margin-top: 7px;

						span {
							display: inline-block;
							background: #e6e6e6;
							padding: 4px 9px;
							font-size: 15px;
							color: #666666;
							cursor: pointer;
							width: 45px;
							text-align: center;
							font-weight: 600;

							&:hover {
								background: #cecece;
							}

							&:first-child {
								border-top-left-radius: 5px;
								border-bottom-left-radius: 5px;
							}

							&:last-child {
								border-top-right-radius: 5px;
								border-bottom-right-radius: 5px;
							}

							& + span {
								margin-left: 2px;
							}
						}
					}
				}
			}

			.main-divider {
				margin: 15px 0;
				position: relative;
				text-align: center;

				span {
					position: relative;
					display: inline-block;
					background: white;
					padding: 0 12px;
					z-index: 1;
					font-weight: 600;
					text-transform: uppercase;
					color: #999999;
					font-size: 16px;
				}

				&:after {
					content: '';
					position: absolute;
					top: 12px;
					width: 100%;
					height: 0;
					border-bottom: 1px solid #cccccc;
					z-index: 0;
					left: 0;
				}
			}
		}


		.actions {
			margin-top: 7px;

			span {
				cursor: pointer;
				font-size: 16px;
				color: #b3b3b3;

				&:hover {
					color: #777777;
				}

				i {
					margin-right: 3px;
					font-size: 13px;
				}
			}
		}
	}
</style>
