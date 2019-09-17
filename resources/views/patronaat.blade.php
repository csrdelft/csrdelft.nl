@section('titel', 'Patronaat')
	<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
</head>
<body>

<div class="container">

	@php($cats = ['student_assistent' => 'Student assistent', 'verticale_vriend' => 'Verticale vriend', 'gids_reisleider' => 'Gids/Reisleider', 'mentor' => 'Mentor'])
	@foreach($cats as $cat => $catNaam)
		<div class="page" style="clear: both">
			<h1>{{$catNaam}}</h1>
			<div class="" style="clear: both;">
				@foreach($groep->getLeden() as $deelnemer)
					@if($deelnemer->opmerking2)
						@foreach($deelnemer->opmerking2 as $opmerking)
							@if($opmerking->naam == $cat && $opmerking->selectie != "Nee")
								@php($lid = \CsrDelft\model\ProfielModel::get($deelnemer->uid))
								<div class="" style="width: 33%; padding: 1em; display: inline-block;">
									<div class="card d-block m-2" style="height: 200px;">
										@if (is_zichtbaar($lid, 'profielfoto', 'intern', null)) {{-- geen override, wordt geprint --}}
										<img class="" style="width: 150px; height: 200px; object-fit: cover; float:left;"
												 src="/profiel/pasfoto/{{$lid->uid}}.jpg" alt="">
										@endif
										<div class="card-body" style="float: left; width: calc(100% - 150px);">
											<dl>
												<dt>Naam</dt>
												<dd>{{$lid->getNaam()}}</dd>
												<dt>Lidjaar</dt>
												<dd>{{$lid->lidjaar}}</dd>
												<dt>Studie</dt>
												<dd>{{$lid->studie}}</dd>
											</dl>
											@if($deelnemer->opmerking2[5]->selectie == false)
												<div class="text-muted small">Niet aanwezig op startkamp</div>
												@endif
										</div>
									</div>
								</div>
							@endif
						@endforeach
					@endif
				@endforeach
			</div>
		</div>
		<div style="page-break-before: always"></div>
	@endforeach
</div>
</body>
</html>
