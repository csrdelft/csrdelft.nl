@extends('documenten.base')

@section('titel', 'Documenten overzicht')

@section('content')
	<div id="controls">
		@can(P_DOCS_MOD)
			<a class="btn" href="/documenten/toevoegen">@icon('toevoegen') Toevoegen</a>
		@endcan
	</div>

	{!! getMelding() !!}

	<h1>Documenten</h1>

	<table id="documenten" class="table table-striped">
		<thead>
		<tr>
			<th scope="col">Document</th>
			<th scope="col">Grootte</th>
			<th scope="col">Type</th>
			<th scope="col">Toegevoegd</th>
			<th scope="col">Eigenaar</th>
		</tr>
		</thead>

		@forelse($categorieen as $categorie)
			<tbody>
			<tr class="table-primary">
				<td colspan="5">
					<a href="/documenten/categorie/{{$categorie->id}}" title="Alle documenten in {{$categorie->naam}}">
						{{ $categorie->naam }}
					</a>
					@can(P_DOCS_MOD)
						<a class="toevoegen float-right" href="/documenten/toevoegen?catID={{$categorie->id}}"
							 title="Document toevoegen in categorie: {{$categorie->naam}}">
							@icon('toevoegen')
						</a>
					@endcan
				</td>
			</tr>
			@forelse($categorie->documenten->slice(0, 5) as $document)
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
			<th scope="col">Document</th>
			<th scope="col">Grootte</th>
			<th scope="col">Type</th>
			<th scope="col">Toegevoegd</th>
			<th scope="col">Eigenaar</th>
		</tr>
		</tfoot>
	</table>
@endsection
