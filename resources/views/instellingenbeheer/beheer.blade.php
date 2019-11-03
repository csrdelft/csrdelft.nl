@extends('layout')

@section('titel', 'Instellingenbeheer')

@section('content')
	<h1>Instellingenbeheer</h1>
	<p>
		Op deze pagina kunt u instellingen wijzigen en resetten voor elke module op de stek.
		Onderstaande tabel toont alle instellingen van de gekozen module.
	</p>
	<p>
		N.B. Deze instellingen zijn essentieel voor de werking van de stek!
	</p>
	<ul class="nav nav-tabs">
		@foreach($modules as $m)
			<li class="nav-item">
				@link(ucfirst($m), '/instellingenbeheer/module/' . $m, 'nav-link', 'active')
			</li>
		@endforeach
	</ul>
	@if($module)
		<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col">Wijzig</th>
				<th scope="col">Id</th>
				<th scope="col">Waarde</th>
				<th scope="col">Reset</th>
			</tr>
			</thead>
			<tbody>
			@foreach($instellingen as $id)
				@include('instellingenbeheer.regel', ['waarde' => instelling($module, $id), 'id' => $id, 'module' => $module])
			@endforeach
			</tbody>
		</table>
	@endif
@endsection
