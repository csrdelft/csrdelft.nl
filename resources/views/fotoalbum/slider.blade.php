@php($uniqid = uniqid_safe('slider_'))

<div id="{{$uniqid}}" class="carousel slide bb-slider disable-swipe" data-ride="carousel">
	<ol class="carousel-indicators">
		@foreach($fotos as $foto)
			<li data-target="#{{$uniqid}}" data-slide-to="{{$loop->index-1}}"
					@if($loop->first) class="active" @endif></li>
		@endforeach
	</ol>
	<div class="carousel-inner">
		@foreach($fotos as $foto)
			<div class="carousel-item @if($loop->first) active @endif">
				<img style="height: 360px;" src="{{$foto->getResizedUrl()}}" alt="{{$foto->filename}}">
			</div>
		@endforeach
	</div>
	<a class="carousel-control-prev" href="#{{$uniqid}}" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="carousel-control-next" href="#{{$uniqid}}" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
</div>
