@section('titel', $titel)

	<!DOCTYPE html>
<html lang="nl">
	<head>
		@push('styles')
			{!! css_asset('thema-normaal') !!}
		@endpush
		@include('head')
		<meta name="robots" content="noindex">
		<style>
			body {
				margin: 50px 10vw;
				margin: 50px max(20px, 5vw);
			}

			img {
				width: 100px;
			}

			.btn-primary {
				color: white !important;
				padding: 6px 10px;
				margin: 25px auto;
				display: block;
			}

			@media screen and (max-width: 550px) {
				.col-form-label {
					max-width: 100%;
					flex: 100%;
					line-height: 1;
				}

				.col-9 {
					flex: 100%;
					max-width: 100%;
				}
			}
		</style>
	</head>
	<body>

		<div class="container">
			<section id="banner">
				<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
			</section>
			@section('content')
				@if(isset($content))
					@php($content->view())
				@endif
			@show
		</div>
	</body>
</html>
