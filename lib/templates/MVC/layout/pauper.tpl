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
				<span class="waarschuwing">{LoginModel::instance()->getError()}</span>
				{if LoginModel::mag('P_LOGGED_IN')}
					<a href="/logout">Uitloggen</a>
				{else}
					<div class="login-form">{include file='csrdelft2/partials/_loginForm.tpl'}</div>
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
				{foreach from=$menutree->children item=item}
					<li>
						<h2>
							<a href="{$item->link}">
								{$item->tekst}
							</a>
						</h2>
						<ul>
							{foreach from=$item->children item=subitem}
								<li>
									<a href="{$subitem->link}">
										{$subitem->tekst}
									</a>
								</li>
							{/foreach}
						</ul>
					</li>
				{/foreach}
			</ul>
		</div>
	</body>
</html>