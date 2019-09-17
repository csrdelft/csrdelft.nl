@extends('layout')

@section('titel', 'Menubeheer')

@section('content')
	@if($menus)
		<div class="float-right form-inline">
			<label for="menu-select">Toon menu:</label>
			<select name="toon" id="menu-select" onchange="location.href = '/menubeheer/beheer/' + this.value;" class="form-control">
				<option selected="selected">kies</option>
				@foreach($menus as $item)
					<option value="{{$item->tekst}}">{{$item->tekst}}</option>
				@endforeach
			</select>
		</div>
	@endif
	<h1>Menubeheer</h1>
	<ul class="menubeheer-tree">
		@if($root)
			<li>
				@include('menubeheer.root', ['root' => $root])
			</li>
			@if($root->children)
				@foreach($root->children as $child)
					@include('menubeheer.item', ['item' => $child])
				@endforeach
			@endif
		@endif
	</ul>
@endsection
