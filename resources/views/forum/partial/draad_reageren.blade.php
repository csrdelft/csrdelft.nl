@auth
	<ul class="forum-reageren">
		@forelse($reageren as $react)
			<li class="reagerenLid"
					title="{{\CsrDelft\repository\ProfielRepository::getNaam($react->uid, 'user')}} is een reactie aan het schrijven">
				@icon('comment_edit') {{\CsrDelft\repository\ProfielRepository::getNaam($react->uid, 'user')}}</li>
		@empty
			{{--<li class="reagerenLid"><br/></li>--}}
		@endforelse
	</ul>
@endauth
