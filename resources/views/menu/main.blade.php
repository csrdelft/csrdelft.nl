<nav id="menu" class="navbar navbar-expand-md navbar-dark">
	<a class="nav-option trigger text-white d-block d-lg-none" href="#zijbalk"><span class="sr-only">Zijbalk openen</span><i
			class="fa fa-lg fa-fw fa-bookmark"></i></a>
	<a class="navbar-brand" href="/">
		<img src="/images/beeldmerk-wit.png" alt="Beeldmerk C.S.R. Delft"/>
		<h2>C.S.R. Delft</h2>
	</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
					aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div id="navbarNav" class="collapse navbar-collapse">
		<ul id="cd-primary-nav" class="navbar-nav">
			@can(P_LOGGED_IN)
				@include('menu.main_tree', ['parent' => $root])
			@elsecan
				<li><a href="/">Log in</a></li>
			@endcan
		</ul>
		<div class="navbar-nav navbar-search ml-auto mr-3">
			@php((new \CsrDelft\view\formulier\InstantSearchForm())->view())
		</div>
		<ul class="navbar-nav">
			@foreach($root->getChildren() as $item)
				@if($item->tekst == 'Personal')
					@include('menu.personal', ['parent' => $item])
				@endif
			@endforeach
		</ul>
	</div>
</nav>

