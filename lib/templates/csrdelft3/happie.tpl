<!DOCTYPE html>
<html>
	<head>
		{include file='html_head.tpl'}
	</head>
	<body role="document">
		<div class="navbar navbar-inverse" role="navigation"><!-- navbar-fixed-top -->
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="/happie/bestel/nieuw">Nieuwe bestelling</a>
					<a class="navbar-brand" href="/happie/bestel/serveer">Serveer</a>
				</div>
				<div class="navbar-collapse collapse" style="max-height:none;">
					<ul class="nav navbar-nav">
						<li><a href="/happie/bestel/keuken">Keuken</a></li>
						<li><a href="/happie/bestel/bar">Bar</a></li>
						<li><a href="/happie/bestel/kassa">Kassa</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Administratie <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="/happie/bestel/overzicht">Alle bestellingen</a></li>
								<li class="divider"></li>
								<li class="dropdown-header">Menukaart aanpassen</li>
								<li><a href="/happie/menukaart">Menukaart-items</a></li>
								<li><a href="/happie/menugroep">Menukaart-groepen</a></li>
							</ul>
						</li>
					</ul>
				</div><!-- close navbar-collapse -->
			</div>
		</div>
		{$body->view()}
		{if $smarty.const.DEBUG AND (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())}
			<a id="mysql_debug_toggle" onclick="$(this).replaceWith($('#mysql_debug').toggle());">DEBUG</a>
			<div id="mysql_debug" class="pre">{getDebug()}</div>
		{/if}
	</body>
</html>