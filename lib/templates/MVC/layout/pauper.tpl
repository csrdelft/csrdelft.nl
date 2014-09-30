<!DOCTYPE html>
<html>
	<head>
		<title>C.S.R. Delft mobiel - {$body->getTitel()}</title>
		{include file='html_head.tpl'}
	</head>
	<body style="background: none;">
		<div style="text-align: center;">
			<div style="float: left;">
				<a href="#mainmenu">Menu</a>
			</div>
			<a href="/pauper/terug">Naar normale webstek</a>
			<div style="float: right;">
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
		<div id="paupermenu" style="clear: both;">
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