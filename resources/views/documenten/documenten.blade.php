@extends('documenten.base')

@section('titel', 'Documenten overzicht')

@section('content')
	<div id="controls">
		@can('P_DOCS_MOD')
			<a class="btn" href="/documenten/toevoegen">@icon('toevoegen') Toevoegen</a>
		@endcan
	</div>

	{!! getMelding() !!}

	<h1>Documenten</h1>

	<table id="documenten" class="documenten">
		<thead>
		<tr>
			<th>Document</th>
			<th>Grootte</th>
			<th>Type</th>
			<th>Toegevoegd</th>
			<th>Eigenaar</th>
		</tr>
		</thead>

		@forelse($categorieen as $categorie)
			<tbody>
			<tr>
				<th colspan="5">
					<a href="/documenten/categorie/{{$categorie->id}}/" title="Alle documenten in {{$categorie->naam}}">
						{{ $categorie->naam }}
					</a>
					@can('P_DOCS_MOD')
						<a class="toevoegen" href="/documenten/toevoegen/?catID={{$categorie->id}}"
							 title="Document toevoegen in categorie: {{$categorie->naam}}">
							@icon('toevoegen')
						</a>
					@endcan
				</th>
			</tr>
			@forelse($model->getRecent($categorie, 5) as $document)
				@include('documenten.documentregel', ['document' => $document])
			@empty
				<tr>
					<td class="document" colspan="5">Geen documenten in deze categorie</td>
				</tr>
			@endforelse
			</tbody>
		@empty
			<tr>
				<td colspan="5">Geen categorieën in de database aanwezig.</td>
			</tr>
		@endforelse
		<tfoot>
		<tr>
			<th>Document</th>
			<th>Grootte</th>
			<th>Type</th>
			<th>Toegevoegd</th>
			<th>Eigenaar</th>
		</tr>
		</tfoot>
	</table>
@endsection
