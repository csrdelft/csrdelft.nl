@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-owee.layout')

@section('styles')
	@stylesheet('extern.css')
	@stylesheet('extern-fotoalbum.css')
@endsection
@endguest

@section('titel', ucfirst($album->dirname))

@section('breadcrumbs')
	{!! \CsrDelft\view\fotoalbum\FotoAlbumBreadcrumbs::getBreadcrumbs($album) !!}
@endsection

@section('content')
	<div id="contextMenu" class="dropdown-menu" role="menu"></div>
	<div id="tagMenu" class="dropdown-menu" role="menu"></div>
	<div class="fotoalbum"
			 data-fotos="{!! htmlspecialchars(json_encode($album->getAlbumArrayRecursive())) !!}"
			 data-is-logged-in="{{ \CsrDelft\model\security\LoginModel::mag('P_LOGGED_IN') }}"
			 data-mag-aanpassen="{{ json_encode($album->magAanpassen()) }}"
			 data-root="{{ CSR_ROOT . '/plaetjes' }}"
			 data-slideshow-interval="{{ \CsrDelft\model\InstellingenModel::get('fotoalbum', 'slideshow_interval') }}">
		<div class="fotoalbum float-right">
			@if($album->magToevoegen())
				<a class="btn" href="/fotoalbum/uploaden/{{$album->subdir}}">@icon('picture_add') Toevoegen</a>
				<a class="btn post popup" href="/fotoalbum/toevoegen/{{$album->subdir}}">@icon('folder_add') Nieuw album</a>
			@endif
			@if($album->magAanpassen())
				<a href="/fotoalbum/hernoemen/{{$album->subdir}}" class="btn post prompt redirect" title="Fotoalbum hernoemen"
					 data="Nieuwe naam={{ucfirst($album->dirname)}}">@icon('pencil') Naam wijzigen</a>
				@if($album->isEmpty())
					<a href="/fotoalbum/verwijderen/{{$album->subdir}}" class="btn post confirm redirect"
						 title="Fotoalbum verwijderen">@icon('cross') Verwijderen</a>
				@endif
				@can($album->magAanpassen())
					<a class="btn popup confirm" href="/fotoalbum/verwerken/{{$album->subdir}}"
						 title="Fotoalbum verwerken (dit kan even duren). Verwijder magick-* files in /tmp handmatig bij timeout!">@icon('application_view_gallery')
						Verwerken</a>
				@endcan
			@endif
			@can('P_LOGGED_IN')
				@if($album->hasFotos())
					<a class="btn" href="/fotoalbum/downloaden/{{$album->subdir}}"
						 title="Download als TAR-bestand">@icon('picture_save') Download album</a>
				@endif
			@endcan
		</div>
		<h1 class="inline">{{ucfirst($album->dirname)}}</h1>
		@if($album->hasFotos())
			<div id="gallery" class="gallery">
			</div>
		@else
			<div class="subalbums">
				@foreach($album->getSubAlbums() as $subAlbum)
					@php($cover_url = $subAlbum->getCoverUrl())
					<div class="subalbum">
						<a href="{{$subAlbum->getUrl()}}#{{preg_replace('/_thumbs/', '/_resized/', $subAlbum->getCoverUrl())}}">
							<img src="{{$subAlbum->getCoverUrl()}}" alt="{{ucfirst($subAlbum->dirname)}}"/>
							<div class="subalbumname">{{ucfirst($subAlbum->dirname)}}</div>
						</a>
					</div>
				@endforeach
			</div>
		@endif
	</div>
@endsection
