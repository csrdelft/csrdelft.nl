@extends('layout-extern.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern.css')
	@stylesheet('extern-owee.css')
	@script('extern-owee.js')
@endsection

@section('body')
	<!-- Banner -->
	<section id="banner">
		<div class="inner">
			<a href="/">
				<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
				<h1>C.S.R. Delft</h1>
			</a>
		</div>
	</section>

	<div class="owee">
		<div class="bbl atl hero">
			<div class="content"></div>
		</div>

		<div class="content">

		</div>

		<div class="bbl atl buttons">
			<div class="content"></div>
		</div>

		<div class="content"></div>

		<div class="bbl atl interest">
			<div class="content"></div>
		</div>

		<div class="content">

		</div>

		<div class="bbl atl notes">
			<div class="content"></div>
		</div>
	</div>

	<!-- Footer -->
	<div id="wrapper">
		<section id="footer">
			<div class="inner">
				<ul class="copyright">
					<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
							href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
				</ul>
			</div>
		</section>
	</div>
@endsection
