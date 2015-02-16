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
			<ul>
				{foreach from=$mainmenu->getChildren() item=item}
					{if $item->magBekijken()}
						<li>
							<h3>
								<a href="{$item->link}">
									{$item->tekst}
								</a>
							</h3>
							<ul>
								{foreach from=$item->getChildren() item=subitem}
									{if $subitem->magBekijken()}
										<li>
											<a href="{$subitem->link}">
												{$subitem->tekst}
											</a>
										</li>
									{/if}
								{/foreach}
							</ul>
						</li>
					{/if}
				{/foreach}
			</ul>
		</div>
	</body>
</html>