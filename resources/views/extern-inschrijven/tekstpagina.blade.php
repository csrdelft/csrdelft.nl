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
				margin: 50px 10vw;
				margin: 50px max(20px, 5vw);
			}

			img {
				width: 100px;
			}
		</style>
	</head>
	<body>

		<div class="container">
			<section id="banner">
				<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
			</section>

			{!! $content !!}
		</div>
	</body>
</html>
