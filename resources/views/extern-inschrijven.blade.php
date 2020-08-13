@section('titel', $titel)

<!DOCTYPE html>
<html lang="nl">
	<head>
		@push('styles')
			{!! css_asset('thema-normaal') !!}
		@endpush
		@include('head')
		<style>
			body {
				margin: 50px 200px;
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
