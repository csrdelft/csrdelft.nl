<template>
	<div class="container stekpakket">
		<div class="row align-items-center">
			<div class="col-lg-2"></div>
			<div class="col-auto">
				<img src="/dist/images/beeldmerk.png" alt="C.S.R. Delft" class="beeldmerk">
			</div>
			<div class="col">
				<h1>De prijs van de stek</h1>
				<div class="subtitel">Stel uw stekpakket samen</div>
			</div>
			<div class="col-lg-2"></div>
		</div>
		<div class="row align-items-center">
			<div class="col-lg-2"></div>
			<div class="col-lg">
				<p>
					Hieronder kunt u uw eigen stekpakket samenstellen.
					U selecteert eerst een basispakket en past dit vervolgens aan naar uw smaak.
					De kosten van uw stekpakket worden maandelijks afgeschreven van uw CiviSaldo.
					Het stekpakket kunt u per maand bijstellen.
				</p>
			</div>
			<div class="col-lg-2"></div>
		</div>
		<div class="row equal">
			<div class="col-12"><h2>Kies een basispakket</h2></div>
			<div class="col-xl-3 col-sm-6" v-for="i in [1,2,3,4]">
				<div class="pakket">
					<div class="titel">Randlid</div>
					<div class="usp"><i class="fa fa-check"></i> Forum afgelopen week lezen</div>
					<div class="usp"><i class="fa fa-check"></i> Forum afgelopen week lezen</div>
					<div class="usp"><i class="fa fa-check"></i> Forum afgelopen week lezen</div>
					<div class="usp"><i class="fa fa-check"></i> Forum afgelopen week lezen</div>
					<div class="prijs">
						<div class="getal">0<span>,00</span></div>
						<div class="per">per maand</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import {Component} from 'vue-property-decorator';

	@Component
	export default class StekPakket extends Vue {

	}
</script>

<style scoped lang="scss">
	.stekpakket {
		font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;
	}

	.beeldmerk {
		width: 80px;
	}

	h1 {
		font-size: 20pt;
		font-weight: 600;
		line-height: 24pt;
		margin: 0;
	}

	.subtitel {
		font-size: 14pt;
		font-weight: 300;
	}

	p {
		font-size: 13pt;
		font-weight: 300;
	}

	h2 {
		text-align: center;
		font-size: 17pt;
		font-weight: 600;
		margin-bottom: 15px;
		margin-top: 30px;
	}

	.equal {
		display: flex;
		display: -webkit-flex;
		flex-wrap: wrap;
	}

	.pakket {
		border: 1px solid #3498db;
		border-radius: 5px;
		padding: 0 20px;
		text-align: center;
		margin-bottom: 30px;
		min-height: calc(100% - 30px);
		background: white;
		color: black;
		cursor: pointer;

		&:hover, &:focus {
			background: #3498db;
			color: white;
			border-top-width: 0;

			.titel {
				background: white;
				color: #3498db;
				padding-top: 3px;
			}

			.usp i {
				color: white;
			}
		}

		.titel {
			margin-bottom: 15px;
			background: #3498db;
			border-radius: 0 0 3px 3px;
			color: white;
			font-size: 10pt;
			font-weight: 600;
			display: inline-block;
			padding: 2px 12px;
		}

		.usp {
			font-size: 11pt;
			font-weight: 300;
			text-align: left;
			line-height: 17pt;

			i {
				color: #2ECC71;
				margin-right: 8px;
			}
		}

		.prijs {
			.getal {
				span {

				}
			}

			.per {

			}
		}
	}
</style>
