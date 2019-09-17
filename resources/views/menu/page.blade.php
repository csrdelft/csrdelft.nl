<ul class="horizontal">
	@foreach($root->children as $item)
		@if($item->magBekijken())
			<li class="item @if($item->active) active @endif">
				&raquo; <a href="{{$item->link}}" title="{{$item->tekst}}">{{$item->tekst}}</a>
			</li>
		@endif
	@endforeach
</ul>
<hr/>
<table>
	<tr id="melding">
		<td id="melding-veld">{!! getMelding() !!}</td>
	</tr>
</table>
<h1>{{$titel}}</h1>

