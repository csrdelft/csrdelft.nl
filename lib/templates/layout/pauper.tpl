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
				{if LoginModel::instance()->hasError()}
					<span class="error">{LoginModel::instance()->getError()}</span>
				{/if}
				{if LoginModel::mag('P_LOGGED_IN')}
					<a href="/logout">Uitloggen</a>
				{else}
					<div class="login-form">{include file='layout2/partials/_loginForm.tpl'}</div>
				{/if}
			</div>
		</div>
		<br />
		{$body->view()}
		<br />
		<div id="paupermenu" class="clear">
			<br />
			<a name="mainmenu"><h1>Menu</h1></a>
			{$mainmenu->view()}
		</div>
	</body>
</html>