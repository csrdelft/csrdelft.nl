<nav id="menu" class="navbar navbar-expand-lg navbar-dark bg-primary">
	<a class="navbar-brand" href="/">C.S.R. Delft</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
					aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse">
		<ul id="cd-primary-nav" class="navbar-nav">
			@can(P_LOGGED_IN)
				@include('menu.main_tree', ['parent' => $root])
			@elsecan
				<li><a href="/">Log in</a></li>
			@endcan
		</ul>
	</div>
	@php((new \CsrDelft\view\formulier\InstantSearchForm())->view())
{{--	<form class="form-inline">--}}
{{--		<input class="form-control mr-sm-2" type="search" placeholder="Zoeken" aria-label="Zoeken">--}}
{{--		<button class="btn btn-outline-success my-2 my-sm-0" type="submit"><i class="fa fa-search"></i></button>--}}
{{--	</form>--}}
</nav>

