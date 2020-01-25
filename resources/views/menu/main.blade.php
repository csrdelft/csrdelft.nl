<nav id="menu" class="navbar navbar-expand-md navbar-dark bg-primary">
	<a class="nav-option trigger text-white d-block d-lg-none" href="#zijbalk"><span class="sr-only">Zijbalk openen</span><i
			class="fa fa-lg fa-fw fa-bookmark"></i></a>
	<a class="navbar-brand d-block d-md-none" href="/">C.S.R. Delft</a>
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
		<div class="navbar-nav ml-auto">
			@php((new \CsrDelft\view\formulier\InstantSearchForm())->view())
		</div>
	</div>
</nav>

