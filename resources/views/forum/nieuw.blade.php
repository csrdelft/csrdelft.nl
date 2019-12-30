@extends('forum.base')

@section('titel', 'Nieuw draad')

@section('breadcrumbs')
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="/" title="Thuis"><span class="fa fa-home"></span></a></li>
		<li class="breadcrumb-item"><a href="/forum">Forum</a></li>
		<li class="breadcrumb-item active"><select name="forum_id" class="form-control form-control-sm"
																							 onchange="if (this.value.substr(0,4) === 'http') { window.open(this.value); } else { window.location.href = this.value; }">
				<option value="/forum/belangrijk">Belangrijk recent gewijzigd</option>
				<option value="/forum/recent">Recent gewijzigd</option>

				@foreach($categorien as $categorie)
					<optgroup label="{{$categorie->titel}}">;
						@foreach ($categorie->getForumDelen() as $newDeel) {
						<option value="/forum/deel/{{$newDeel->forum_id}}">{{$newDeel->titel}}</option>
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

	<h1>Nieuw forumdraad</h1>

	<div class="container">
		<div id="forumPosten" class="forum-posten">
			@php($postform->view())
		</div>
	</div>

	@include('forum.partial.rss_link')
@endsection
