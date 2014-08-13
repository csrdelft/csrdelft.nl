<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>C.S.R. Delft mobiel | {$body->getTitel()}</title>
		{include file='csrdelft_head.tpl'}
	</head>
	<body style="background: none;">
		<a href="/pauper/terug">Naar normale webstek</a>
		<div style="float: right;">
			{if isset(LoginModel::instance()->getError())}
				<span class="waarschuwing">{LoginModel::instance()->getError()}</span>
			{/if}
			{if LoginModel::mag('P_LOGGED_IN')}
				<a href="/logout">Uitloggen</a>
			{else}
				<a href="/login">Inloggen</a>
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