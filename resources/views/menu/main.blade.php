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
		<ul id="cd-primary-nav" class="navbar-nav flex-wrap">
			@can(P_LOGGED_IN)
				@include('menu.main_tree', ['parent' => $root])
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="menu-favorieten" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Favorieten
					</a>
					<ul class="dropdown-menu" aria-labelledby="menu-favorieten">
						@include('menu.sub_tree', ['parent' => $favorieten])
						<li><a href="/menubeheer/toevoegen/favoriet" class="dropdown-item post popup addfav"
							   title="Huidige pagina toevoegen aan favorieten"><i class="fa fa-plus"></i> Favoriet toevoegen</a></li>
					</ul>
				</li>
			@elsecan
				<li><a href="/">Log in</a></li>
			@endcan
		</ul>
		<div class="navbar-nav navbar-search ml-auto mr-3">
			@php((new \CsrDelft\view\formulier\InstantSearchForm())->view())
		</div>
		<ul class="navbar-nav">
			@foreach($root->children as $item)
				@if($item->tekst == 'Personal')
					@include('menu.personal', ['parent' => $item])
				@endif
			@endforeach
		</ul>
	</div>
</nav>
@if(time() < strtotime("2-4-2020"))
	<a class="notice" href="/stekpakket">
		U heeft
		@if(date('j') == 30)
			nog 3 dagen
		@endif
		@if(date('j') == 31)
			nog 2 dagen
		@endif
		@if(date('j') == 1)
			alleen vandaag nog
		@endif
		om uw stekpakket te configureren.</a>
	<style>
		.notice {
			display: block;
			background: #e59200;
			margin-left: -200px;
			z-index: 1000;
			color: white;
			padding: 8px 18px 11px;
			font-size: 15px;
			line-height: 16px;
			font-weight: bold;
			text-align: center;
		}

		.notice:hover {
			color: white;
			text-decoration: none;
		}

		@media (max-width: 991.98px) {
			.notice {
				margin-left: 0;
			}
		}
	</style>
@endif
@if(rand(1,3) ===  1 && time() < strtotime('2-4-2020'))
	<style>
		body {
			-moz-transform: scaleX(-1);
			-o-transform: scaleX(-1);
			-webkit-transform: scaleX(-1);
			transform: scaleX(-1);
			filter: FlipH;
			-ms-filter: "FlipH";
			margin-left: 0;
			margin-right: 200px;
		}

		@media (max-width: 991.98px) {
			body {
				margin-right: 0;
			}
		}
	</style>
@endif
