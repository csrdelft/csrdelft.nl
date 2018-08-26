@extends('forum.base')

@section('title', $deel->titel)

@section('breadcrumbs')
	<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>
	@if($deel->categorie_id)
		» {{$deel->getForumCategorie()->titel}}
	@endif
	» <select name="forum_id"
						onchange="if (this.value.substr(0,4) === 'http') { window.open(this.value); } else { window.location.href = this.value; }">
		<option value="/forum/recent/belangrijk"
						@if($deel->titel === 'Belangrijk recent gewijzigd')selected="selected"@endif>
			Belangrijk recent gewijzigd
		</option>
		<option value="/forum/recent" @if($deel->titel === 'Recent gewijzigd')selected="selected"@endif>
			Recent gewijzigd
		</option>

		@foreach(\CsrDelft\model\forum\ForumModel::instance()->getForumIndelingVoorLid() as $categorie)
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
	</select>
@endsection

@section('content')
	{!! getMelding() !!}

	<div class="forum-header">
		<h1>{{$deel->titel}}</h1>

		@php($zoekform->view())

		@can('P_ADMIN')
			@if(isset($deel->forum_id))
				<div class="forumheadbtn">
					<a href="/forum/beheren/{{$deel->forum_id}}" class="btn post popup"
						 title="Deelforum beheren">@icon('wrench_orange') Beheren</a>
				</div>
			@endif
		@endcan

		@include('forum.partial.head_buttons')
	</div>


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
                'url_append' => $belankrijk,
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
			<div class="breadcrumbs float-right">@yield('breadcrumbs')</div>
			<h2>{{$deel->titel}}</h2>
			{{$deel->omschrijving}}

			@auth
				@if(!isset($deel->forum_id))
					Berichten per dag: (sleep om te zoomen)
					<div class="grafiek">
						{{-- forum.js pikt dit op en vult met een grafiekje. --}}
						<div id="stats_grafiek_overview" style="height: 200px;"></div>
						<div id="stats_grafiek_details" style="height: 500px;"></div>
					</div>
				@endif
			@endauth
		</div>


		@if($deel->magPosten())
			@include('forum.partial.post_form', ['draad' => null])
		@endif
	</div>

	@include('forum.partial.rss_link')
@endsection
