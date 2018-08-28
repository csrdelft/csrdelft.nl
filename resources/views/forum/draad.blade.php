@extends('forum.base')

@section('titel', $draad->titel)

@section('breadcrumbs')
	@php($deel = $draad->getForumDeel())
	<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>
	» <span class="active">{{$deel->getForumCategorie()->titel}}</span>
	» <a href="/forum/deel/{{$deel->forum_id}}/{{\CsrDelft\model\forum\ForumDradenModel::instance()->getPaginaVoorDraad($draad)}}#{{$draad->draad_id}}">{{$deel->titel}}</a>
@endsection

@section('content')
	{!! getMelding() !!}
	<div class="forum-header">
		@php($zoekform->view())

		@section('kop')
			@auth
				@if(!$statistiek && $draad->magStatistiekBekijken())
					<a
						href="/forum/onderwerp/{{$draad->draad_id}}/{{CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}}/statistiek"
						class="btn btn-light" title="Toon statistieken">@icon('chart_line')</a>
					&nbsp;&nbsp;&nbsp;
				@endif
				<a title="Onderwerp toevoegen aan favorieten" class="btn btn-light post popup addfav"
					 href="/menubeheer/toevoegen/favoriet">@icon('start')</a>
				&nbsp;&nbsp;&nbsp;
				@if($draad->isGevolgd())
					<a href="/forum/volgenuit/{{$draad->draad_id}}" class="btn btn-light post ReloadPage volgenUit"
						 title="Onderwerp niet meer volgen per email">@icon('email_go', 'email_delete')</a>
				@elseif($draad->magVolgen())
					<a href="/forum/volgenaan/{{$draad->draad_id}}" class="btn btn-light post ReloadPage volgenAan"
						 title="Onderwerp volgen per email">@icon('email', 'email_add')</a>
				@endif
				&nbsp;&nbsp;&nbsp;
				@if($draad->isVerborgen())
					<a href="/forum/tonen/{{$draad->draad_id}}" class="btn btn-light post ReloadPage tonenAan"
						 title="Onderwerp tonen in zijbalk">@icon('layout', 'layout_add')</a>
				@elseif($draad->magVerbergen())
					<a href="/forum/verbergen/{{$draad->draad_id}}" class="btn btn-light post ReloadPage tonenUit"
						 title="Onderwerp verbergen in zijbalk">@icon('layout_sidebar', 'layout_delete')</a>
				@endif
				&nbsp;&nbsp;&nbsp;
				@if($draad->magModereren())
					@if($draad->gesloten)
						<a href="/forum/wijzigen/{{$draad->draad_id}}/gesloten" class="btn btn-light post ReloadPage slotjeUit"
							 title="Openen (reactie mogelijk)">@icon('lcok', 'lock_break')</a>
					@else
						<a href="/forum/wijzigen/{{$draad->draad_id}}/gesloten" class="btn btn-light post ReloadPage slotjeAan"
							 title="Sluiten (geen reactie mogelijk)">@icon('lock_open', 'lock')</a>
					@endif
					&nbsp;&nbsp;&nbsp;
					<a class="btn btn-light modfuncties" title="Moderatie-functies weergeven" onclick="$('#forumtabel a.forummodknop').fadeIn();
					$('#modereren').slideDown();
					$(window).scrollTo('#modereren', 600, {
						easing: 'easeInOutCubic',
						offset: {
							top: -100,
							left: 0
						}
					});
				 ">@icon('wrench') Modereren</a>
				@endif
			@endauth
			<h1>
				{{$draad->titel}}
				@if($statistiek)
					&nbsp;&nbsp;&nbsp;
					<span class="lichtgrijs small" title="Aantal lezers">{{$draad->getAantalLezers()}} lezers</span>
				@endif
			</h1>
		@endsection

		@yield('kop')

		@if($draad->magModereren())
			@include('forum.partial.draad_mod')
		@endif
	</div>

@section('magreageren')
	@if($draad->verwijderd)
		<div class="draad-verwijderd">Dit onderwerp is verwijderd.</div>
	@elseif($draad->gesloten)
		<div class="draad-gesloten">
			U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
			@if($draad->getForumDeel()->isOpenbaar() && strtotime($draad->laatst_gewijzigd) < strtotime(\CsrDelft\model\InstellingenModel::get('forum', 'externen_geentoegang_gesloten')))
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
		@include('forum.partial.post_form', ['deel' => $draad->getForumDeel()])
	@endif
</div>

@include('forum.partial.rss_link')
@endsection
