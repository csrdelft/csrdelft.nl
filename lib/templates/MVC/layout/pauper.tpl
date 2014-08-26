<!DOCTYPE html>
<html>
	<head>
		<title>C.S.R. Delft mobiel - {$body->getTitel()}</title>
		{include file='csrdelft_head.tpl'}
	</head>
	<body style="background: none;">
		<a href="/pauper/terug">Naar normale webstek</a>
		<div style="float: right;">
			<span class="waarschuwing">{LoginModel::instance()->getError()}</span>
			{if LoginModel::mag('P_LOGGED_IN')}
				<a href="/logout">Uitloggen</a>
			{else}
				<div class="login-form">{include file='csrdelft2/partials/_loginForm.tpl'}</div>
			{/if}
		</div>
		{$body->view()}
		<br />
		<div id="paupermenu" style="clear: both;">
			<br />
			<h1>Menu</h1>
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