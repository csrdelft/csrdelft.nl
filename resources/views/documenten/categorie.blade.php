@extends('documenten.base')

@section('titel')
Documenten in categorie: {{ $categorie->naam }}
@endsection

@section('breadcrumbs')
	{!! csr_breadcrumbs([
	'/' => 'main',
	'/documenten' => 'Documenten',
	'' => $categorie->naam,
	]) !!}
@endsection

@section('content')
	<div id="controls">
		@can(P_DOCS_MOD)
			<a class="btn" href="/documenten/toevoegen?catID={{$categorie->id}}">@icon('toevoegen') Toevoegen</a>
		@endcan
	</div>

	<h1>{{$categorie->naam}}</h1>

	<table id="documentencategorie" class="table table-striped">
		<thead>
		<tr>
			<th scope="col">Document</th>
			<th scope="col">Grootte</th>
			<th scope="col">Type</th>
			<th scope="col">Toegevoegd</th>
			<th scope="col">Eigenaar</th>
		</tr>
		</thead>
		<tbody>
		@forelse($documenten as $document)
			@include('documenten.documentregel', ['document' => $document])
		@empty
			<tr>
				<td class="document" colspan="5">Geen documenten in deze categorie.</td>
			</tr>
		@endforelse
		</tbody>
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
