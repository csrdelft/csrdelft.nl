@extends('forum.base')

@section('titel', $deel->titel)

@section('breadcrumbs')
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/" title="Thuis"><span class="fa fa-home"></span></a></li>
		<li class="breadcrumb-item"><a href="/forum">Forum</a></li>
		<li class="breadcrumb-item active"><select name="forum_id" class="form-control form-control-sm"
							onchange="if (this.value.substr(0,4) === 'http') { window.open(this.value); } else { window.location.href = this.value; }">
			<option value="/forum/belangrijk"
							@if($deel->titel === 'Belangrijk recent gewijzigd')selected="selected"@endif>
				Belangrijk recent gewijzigd
			</option>
			<option value="/forum/recent" @if($deel->titel === 'Recent gewijzigd')selected="selected"@endif>
				Recent gewijzigd
			</option>

			@foreach($categorien as $categorie)
				<optgroup label="{{$categorie->titel}}">;
					@foreach ($categorie->getForumDelen() as $newDeel) {
					<option value="/forum/deel/{{$newDeel->forum_id}}"
									@if ($newDeel->forum_id === $deel->forum_id)selected="selected"@endif>{{$newDeel->titel}}</option>
					@endforeach
				</optgroup>
			@endforeach
			@foreach(\CsrDelft\model\MenuModel::instance()->getMenu('remotefora')->getChildren() as $remotecat)
				@if($remotecat->magBekijken())
					<optgroup label="{{$remotecat->tekst}}">
						@foreach($remotecat->getChildren() as $remoteforum)
							@if($remoteforum->magBekijken())
								<option value="{{$remoteforum->link}}">{{$remoteforum->tekst}}</option>
							@endif
						@endforeach
					</optgroup>
				@endif
			@endforeach
			</select></li>
	</ol>
@endsection

@section('content')
	{!! getMelding() !!}

	<div class="forum-header btn-toolbar">
		@can(P_ADMIN)
			@if(isset($deel->forum_id))
				<div class="btn-group mr-2">
					<a href="/forum/beheren/{{$deel->forum_id}}" class="btn btn-light post popup"
						 title="Deelforum beheren">@icon('wrench_orange') Beheren</a>
				</div>
			@endif
		@endcan
		@include('forum.partial.head_buttons')
		@php($zoekform->view())
	</div>

	<h1>{{$deel->titel}}</h1>

	<div class="forum-deel">
		<div class="header">Titel</div>
		<div class="header">Laatste wijziging</div>
		<div class="header"></div>

		@if(!$deel->hasForumDraden())
			<div>Dit forum is nog leeg.</div>
		@endif

		@foreach($deel->getForumDraden() as $draad)
			@include('forum.partial.draad_lijst', ['draad' => $draad])
		@endforeach

		@if($paging)
			<div class="paging">
				@if(isset($deel->forum_id))
					{!! sliding_pager([
              'baseurl' => "/forum/deel/$deel->forum_id/",
              'pagecount' => \CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas($deel->forum_id),
              'curpage' => \CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina(),
              'separator' => ' &nbsp;',
              'show_prev_next' => true
              ]) !!}
				@else
					{!! sliding_pager([
                'baseurl' => '/forum/recent/',
                'url_append' => $belangrijk,
                'pagecount' => \CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas(),
                'curpage' => \CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina(),
                'separator' => ' &nbsp;'
                ]) !!}
					&nbsp;
					<a
						href="/forum/recent/{{CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas(null)}}{{$belangrijk}}">verder
						terug</a>
				@endif
			</div>
		@endif


		<div class="forumdeel-omschrijving">
			<div class="breadcrumbs">@yield('breadcrumbs')</div>
			<h2>{{$deel->titel}}</h2>
			{{$deel->omschrijving}}

			@auth
				@if(!isset($deel->forum_id))
					<div>
						Berichten per dag:
						<div class="grafiek">
							{{-- forum.ts pikt dit op en vult met een grafiekje. --}}
							<div id="stats_grafiek_overview" class="ctx-graph-line" data-url="/forum/grafiekdata/overview"
									 style="height: 200px;"></div>
						</div>
					</div>
				@endif
			@endauth
		</div>


		@if($deel->magPosten())
			@include('forum.partial.draad_reageren')
			@auth
				<div class="meldingen">
					<div id="draad-melding" class="alert alert-warning">
						Hier kunt u een onderwerp toevoegen in deze categorie van het forum.
						Kijkt u vooraf goed of het onderwerp waarover u post hier wel thuishoort.
					</div>
				</div>
			@endauth
			@include('forum.partial.post_form', ['draad' => null])
		@endif
	</div>

	@include('forum.partial.rss_link')
@endsection
