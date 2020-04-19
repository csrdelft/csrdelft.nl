<ul>
	@foreach($profielen as $profiel)
		<li>
			<a href="/profiel/{{$profiel->uid}}" title="{{$profiel->getNaam('volledig')}}">
				<div class="d-flex flex-column align-items-center">
					{!! $profiel->getPasfotoTag() !!}
					{{$profiel->getNaam('civitas')}}
				</div>
			</a>
			@if($profiel->hasKinderen())
				@include('profiel.stamboom_node', ['profielen' => $profiel->kinderen])
			@endif
		</li>
	@endforeach
</ul>
