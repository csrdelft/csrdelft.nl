<li>
	<a href="/profiel/{{$profiel->uid}}" title="{{$profiel->getNaam('volledig')}}">
		<div class="d-flex flex-column align-items-center">
			{!! $profiel->getPasfotoTag() !!}
			{{$profiel->getNaam('civitas')}}
		</div>
	</a>
	@if($profiel->hasKinderen())
		<ul>
			@foreach($profiel->getKinderen() as $kind)
				@include('profiel.stamboom_node', ['profiel' => $kind])
			@endforeach
		</ul>
	@endif
</li>
