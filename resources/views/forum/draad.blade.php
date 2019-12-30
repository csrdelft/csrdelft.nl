@extends('forum.base')

@section('titel', $draad->titel)

@section('breadcrumbs')
	@php($deel = $draad->getForumDeel())
	{!! csr_breadcrumbs([
		'/' => 'main',
		'/forum' => 'Forum',
		'/forum/deel/' . $deel->forum_id => $deel->titel,
		'' => $draad->titel,
	]) !!}
@endsection

@section('content')
	{!! getMelding() !!}
	<div class="forum-header btn-toolbar">

		@section('kop')
			@auth
				@if(!$statistiek && $draad->magStatistiekBekijken())
					<div class="btn-group mr-2">
						<a
							href="/forum/onderwerp/{{$draad->draad_id}}/{{CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}}/statistiek"
							class="btn btn-light" title="Toon statistieken">@icon('chart_line')</a>
					</div>

				@endif
				<div class="btn-group mr-2">
					<a title="Onderwerp toevoegen aan favorieten" class="btn btn-light post popup addfav"
						 href="/menubeheer/toevoegen/favoriet">@icon('heart', 'heart_add')</a>
				</div>

				@if($draad->magMeldingKrijgen())
					<div class="btn-group mr-2">
						<a href="/forum/meldingsniveau/{{$draad->draad_id}}/nooit"
							 class="btn btn-light post ReloadPage melding-nooit @if($meldingsniveau == \CsrDelft\model\entity\forum\ForumDraadMeldingNiveau::NOOIT) active @endif"
							 title="Nooit meldingen ontvangen">@icon('email_delete', 'email_delete')</a>
						<a href="/forum/meldingsniveau/{{$draad->draad_id}}/vermelding"
							 class="btn btn-light post ReloadPage melding-vermelding @if($meldingsniveau == \CsrDelft\model\entity\forum\ForumDraadMeldingNiveau::VERMELDING) active @endif"
							 title="Melding ontvangen als ik genoemd word">@icon('email_error', 'email_error')</a>
						<a href="/forum/meldingsniveau/{{$draad->draad_id}}/altijd"
							 class="btn btn-light post ReloadPage melding-altijd @if($meldingsniveau == \CsrDelft\model\entity\forum\ForumDraadMeldingNiveau::ALTIJD) active @endif"
							 title="Melding ontvangen bij elk nieuw bericht">@icon('email_add', 'email_add')</a>
					</div>
				@endif

				@if($draad->isVerborgen())
					<div class="btn-group mr-2">
						<a href="/forum/tonen/{{$draad->draad_id}}" class="btn btn-light post ReloadPage tonenAan"
							 title="Onderwerp tonen in zijbalk">@icon('layout', 'layout_add')</a>
					</div>
				@elseif($draad->magVerbergen())
					<div class="btn-group mr-2">
						<a href="/forum/verbergen/{{$draad->draad_id}}" class="btn btn-light post ReloadPage tonenUit"
							 title="Onderwerp verbergen in zijbalk">@icon('layout_sidebar', 'layout_delete')</a>
					</div>
				@endif

				@if($draad->magModereren())
					<div class="btn-group mr-2">
						@if($draad->gesloten)
							<a href="/forum/wijzigen/{{$draad->draad_id}}/gesloten" class="btn btn-light post ReloadPage slotjeUit"
								 title="Openen (reactie mogelijk)">@icon('lock', 'lock_break')</a>
						@else
							<a href="/forum/wijzigen/{{$draad->draad_id}}/gesloten" class="btn btn-light post ReloadPage slotjeAan"
								 title="Sluiten (geen reactie mogelijk)">@icon('lock_open', 'lock')</a>
						@endif
					</div>

					<div class="btn-group mr-2">
						<a href="#modereren" class="btn btn-light modfuncties" title="Moderatie-functies weergeven" data-toggle="collapse" onclick="
				 ">@icon('wrench') Modereren</a>
					</div>
				@endif
			@endauth
		@endsection

		@yield('kop')
		@php($zoekform->view())
	</div>
	@if($draad->magModereren())
		@include('forum.partial.draad_mod')
	@endif
	<h1>
		{{$draad->titel}}
		@if($statistiek)
			&nbsp;&nbsp;&nbsp;
			<span class="lichtgrijs small" title="Aantal lezers">{{$draad->getAantalLezers()}} lezers</span>
		@endif
	</h1>
@section('magreageren')
	@if($draad->verwijderd)
		<div class="draad-verwijderd">Dit onderwerp is verwijderd.</div>
	@elseif($draad->gesloten)
		<div class="draad-gesloten">
			U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
			@if($draad->getForumDeel()->isOpenbaar() && strtotime($draad->laatst_gewijzigd) < strtotime(instelling('forum', 'externen_geentoegang_gesloten')))
				<div class="dikgedrukt">Dit externe onderwerp is niet meer toegankelijk voor externen en zoekmachines.</div>
			@endif
		</div>
	@elseif(!$draad->magPosten())
		<div class="draad-readonly">U mag in dit deel van het forum niet reageren.</div>
	@endif
@endsection

<div class="forum-draad">
	@section('paginering')
		<div class="tussenschot"></div>
		<div class="forum-paginering">
			@if($draad->pagina_per_post)
				Bericht:
			@else
				Pagina:
			@endif
			{!! sliding_pager([
          'baseurl' => "/forum/onderwerp/$draad->draad_id/",
          'url_append' => $statistiek ? '/statistiek' : '',
          'pagecount' => \CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id),
          'curpage' => \CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()
          ]) !!}
		</div>
	@endsection
	{{--Paginering boven eerste post op de pagina als de eerste post van het draadje niet plakkerig is of dit de eerste pagina is --}}
	@if($paging && (!$draad->eerste_post_plakkerig || \CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === 1))
		@yield('paginering')
	@endif

	@php($vanaf = false)
	@foreach($draad->getForumPosts() as $post)

		{{-- Als posts gewijzigd zijn zonder draad gewijzigd te triggeren voorkomt $draad->isOngelezen() dat de gele lijn wordt getoond --}}
		@if(!$vanaf && $draad_ongelezen && (!$gelezen_moment || strtotime($post->laatst_gewijzigd) > $gelezen_moment))
			@php($vanaf = true)
			<div class="tussenschot ongelezenvanaf"><a id="ongelezen"></a></div>
		@else
			<div class="tussenschot"></div>
		@endif

		@include('forum.partial.post_lijst', ['post' => $post])

		{{-- Paginering onder eerste plakkerige post op alle pagina's behalve de eerste --}}
		@if($paging && $draad->eerste_post_plakkerig && \CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() !== 1 && $loop->first)
			@yield('paginering')
		@endif
	@endforeach

	{{-- Paginering onderaan pagina --}}
	@if($paging)
		@yield('paginering')
	@endif

	{{-- Geen ongelezen berichten op de laatste pagina betekend in het geheel geen ongelezen berichten --}}

	@if(!$vanaf && \CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === \CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id))
		<div class="tussenschot ongelezenvanaf"><a id="ongelezen"></a></div>
	@else
		<div class="tussenschot"></div>
	@endif

	<div class="magreageren">
		@yield('magreageren')
	</div>

	<div class="breadcrumbs">@yield('breadcrumbs')</div>
	<div class="forum-footer">
		@yield('kop')
	</div>

	@if($draad->magPosten())
		@include('forum.partial.draad_reageren')
		@php($postform->view())
{{--		@include('forum.partial.post_form', ['deel' => $draad->getForumDeel()])--}}
	@endif
</div>

@include('forum.partial.rss_link')
@endsection
