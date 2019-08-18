@extends('layout')

@section('titel', 'Pagina overzicht')

@section('content')
	<h1>CMS paginas</h1>

	<table class="table table-striped">
		<thead>
		<tr>
			<th>Locatie</th>
			<th>Titel</th>
			<th>Laatst gewijzigd</th>
			<th>Rechten bekijken</th>
			<th>Rechten bewerken</th>
			<th>Inline HTML</th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		@foreach($paginas as $pagina)
			@if(!$pagina->titel) @continue @endif
			@cannot($pagina->rechten_bekijken) @continue @endcannot
			<tr>
				<td>/pagina/{{$pagina->naam}}</td>
				<td>{{$pagina->titel}}</td>
				<td>{!! reldate($pagina->laatst_gewijzigd) !!}</td>
				<td>{{$pagina->rechten_bekijken}}</td>
				<td>{{$pagina->rechten_bewerken}}</td>
				<td>@if($pagina->inline_html) @icon('tick') @else @icon('cross') @endif</td>
				<td>
					<div class="btn-group btn-group-sm">
						<a class="btn btn-light" href="/pagina/{{$pagina->naam}}" title="Bekijken">@icon('eye')</a>
						@can($pagina->rechten_bewerken)
							<a class="btn btn-light" href="/pagina/bewerken/{{$pagina->naam}}" title="Bewerken">@icon('bewerken')</a>
						@endcan
						@can(P_ADMIN)
							<a class="btn btn-light" href="/pagina/verwijderen/{{$pagina->naam}}"
								 title="Verwijderen">@icon('delete')</a>
						@endcan
					</div>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
@endsection
