@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-extern.layout')

@section('styles')
	@stylesheet('extern.css')
	@stylesheet('extern-fotoalbum.css')
@endsection
@endguest

@section('titel', ucfirst($album->dirname))

@section('breadcrumbs')
	<ol class="breadcrumb">
		{!! \CsrDelft\view\fotoalbum\FotoAlbumBreadcrumbs::getBreadcrumbs($album) !!}
	</ol>
@endsection

@section('content')
	<div class="float-right">
		@if($album->magToevoegen())
			<a class="btn" href="{{join_paths("/fotoalbum/uploaden", $album->subdir)}}">@icon('picture_add') Toevoegen</a>
			<a class="btn post popup" href="{{join_paths("/fotoalbum/toevoegen", $album->subdir)}}">@icon('folder_add') Nieuw
				album</a>
		@endif
		@if($album->magAanpassen())
			<a href="{{join_paths("/fotoalbum/hernoemen", $album->subdir)}}" class="btn post prompt redirect"
				 title="Fotoalbum hernoemen"
				 data="naam={{ucfirst($album->dirname)}}">@icon('pencil') Naam wijzigen</a>
			@if($album->isEmpty())
				<a href="{{join_paths("/fotoalbum/verwijderen", $album->subdir)}}" class="btn post confirm redirect"
					 title="Fotoalbum verwijderen">@icon('cross') Verwijderen</a>
			@endif
			<a class="btn popup confirm" href="{{join_paths("/fotoalbum/verwerken", $album->subdir)}}"
				 title="Fotoalbum verwerken (dit kan even duren). Verwijder magick-* files in /tmp handmatig bij timeout!">@icon('application_view_gallery')
				Verwerken</a>
		@endif
		@can(P_LOGGED_IN)
			@if($album->hasFotos())
				<a class="btn" href="{{join_paths("/fotoalbum/downloaden", $album->subdir)}}"
					 title="Download als TAR-bestand">@icon('picture_save') Download album</a>
			@endif
		@endcan
	</div>
	<h1 class="inline">{{ucfirst($album->dirname)}}</h1>
	@if($album->hasFotos())
		<div class="fotoalbum disable-swipe"
				 data-fotos="{!! htmlspecialchars(json_encode($album->getAlbumArrayRecursive())) !!}"
				 data-is-logged-in="{{ json_encode(\CsrDelft\model\security\LoginModel::mag(P_LOGGED_IN)) }}"
				 data-mag-aanpassen="{{ json_encode($album->magAanpassen()) }}"
				 data-root="{{ CSR_ROOT . '/fotoalbum' }}"
				 data-slideshow-interval="{{ instelling('fotoalbum', 'slideshow_interval') }}">
		</div>
	@else
		<div class="subalbums">
			@foreach($album->getSubAlbums() as $subAlbum)
				<div class="subalbum">
					<a href="{{$subAlbum->getUrl()}}#{{preg_replace('/_thumbs/', '_resized', $subAlbum->getCoverUrl())}}">
						<img src="{{$subAlbum->getCoverUrl()}}" alt="{{ucfirst($subAlbum->dirname)}}"/>
						<div class="subalbumname">{{ucfirst($subAlbum->dirname)}}</div>
					</a>
				</div>
			@endforeach
		</div>
	@endif
@endsection
