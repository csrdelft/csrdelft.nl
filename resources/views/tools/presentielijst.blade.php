@extends('layout')

@section('titel', 'Presentielijst-tool')

@section('content')
	<div class="container">
		<div class="row border-bottom">
		 <div class="col text-left">
			 <h2> Presentielijst tool </h2>
			</div>
		</div>
		<div class="row mt-3">
			<div class="col">
				<p><em>Deze tool kan worden gebruikt op de volgende manier:</em></p>
				<ul>
					<li>Lorem ipsum</li>
					<li>DOlor</li>
					<li>9dk</li>
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<table class="table">
					@php
					$laatste_status = null;
					foreach($leden as $lid)
					    echo($lid->getNaam()."<br>")
					@endphp

				</table>


		</div>
	</div>
	</div>

		<!-- /.row -->
	<!-- /.container -->
@endsection

