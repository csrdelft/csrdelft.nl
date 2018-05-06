<!DOCTYPE html>
<html>
	<head>
		<title>C.S.R. Delft mobiel - {$body->getTitel()}</title>
		{include file='html_head.tpl'}
	</head>
	<body>
		<div class="text-center">
			<div class="float-left">
				<a href="#mainmenu">Menu</a>
			</div>
			<a href="/pauper/terug">Naar normale webstek</a>
			<div class="float-right">
				{if CsrDelft\model\security\LoginModel::instance()->hasError()}
					<span class="error">{CsrDelft\model\security\LoginModel::instance()->getError()}</span>
				{/if}
				{toegang P_LOGGED_IN}
					<a href="/logout">Uitloggen</a>
				{/toegang}
			</div>
		</div>
		<br />
		{$body->view()}
		<br />
		<div id="paupermenu" class="clear">
			<br />
			<a name="mainmenu"><h2>Menu</h2></a>
			{$mainmenu->view()}
		</div>
	</body>
</html>
