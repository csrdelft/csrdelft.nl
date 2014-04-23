<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><div id="beeldmerk"></div></a></div>
	<div id="menucenter">
		<div id="menubanners">
			<div id="banner1" class="menubanner"></div>
			<div id="banner2" class="menubanner"></div>
			<div id="banner3" class="menubanner"></div>
			<div id="banner4" class="menubanner"></div>
		</div>
		<ul id="mainmenu">
			{foreach name=main from=$root->children item=item}
				<li>
					<a href="{$item->link}" id="top{$smarty.foreach.main.iteration}" onmouseover="StartShowMenu('{$smarty.foreach.main.iteration}');" onmouseout="ResetShowMenu();"{if $item->active} class="active"{/if} title="{$item->tekst}">{$item->tekst}</a>
				</li>
			{/foreach}
		</ul>
	</div>
	<div id="menuright">
		{if LoginLid::mag('P_LOGGED_IN') }
			<div id="ingelogd">
				<a href="/instellingen/" class="instellingen" title="Webstekinstellingen">{icon get="instellingen"}</a>
				{if LoginLid::instance()->isSued()}
					<a href="/endsu/" style="color: red;">{LoginLid::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')} als</a><br />»
				{/if}
				{LoginLid::instance()->getUid()|csrnaam}<br />
				<div id="uitloggen"><a href="/logout.php">log&nbsp;uit</a></div>
				<div id="saldi">
					{foreach from=LoginLid::instance()->getLid()->getSaldi() item=saldo}
						<div class="saldoregel">
							<div class="saldo{if $saldo.saldo < 0 AND LoginLid::instance()->getUid()!='0524'} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
							{$saldo.naam}:
						</div>
					{/foreach}
				</div>
				{if LoginLid::mag('P_LEDEN_MOD')}
					<div id="adminding">
						Beheer
						{if LoginLid::mag('P_ADMIN')}
							{if $forumcount > 0 OR $queues.meded->count()>0}
								({$forumcount}/{$queues.meded->count()})
							{/if}
						{/if}
						<div>
							{if LoginLid::mag('P_ADMIN')}
								<span class="queues">
									<a href="/forum/wacht">Forum: <span class="count">{$forumcount}</span><br /></a>
										{foreach from=$queues item=queue key=name}
										<a href="/tools/query.php?id={$queue->getID()}">
											{$name|ucfirst}: <span class="count">{$queue->count()}</span><br />
										</a>
									{/foreach}
								</span>
								<a href="/su/x101">&raquo; SU Jan Lid.</a><br />
							{/if}
							<a href="/beheer">&raquo; Beheeroverzicht</a><br />
							<a href="/tools/query.php">&raquo; Opgeslagen queries</a><br />
							<a href="/menubeheer">&raquo; Menubeheer</a> <a href="/instellingenbeheer">&raquo; Instellingen</a><br />
						</div>
					</div>
					{literal}
						<script>
							jQuery(document).ready(function($) {
								$('#adminding').click(function() {
									$(this).children('div').toggle();
								});
								$('#adminding div').hide();
							});
						</script>
					{/literal}
				{/if}
				<br />
				<form method="get" action="/communicatie/lijst.php" name="lidzoeker">
					<p>
						{if isset($smarty.get.q)}
							<input type="text" value="{$smarty.get.q|escape:'htmlall'}" name="q" id="zoekveld" />
						{else}
							<input type="text" name="q" id="zoekveld" />
						{/if}
					</p>
				</form>
			</div>
		{else}
			<div id="key"><img src="{$CSR_PICS}/layout/key.png" onclick="$('#login').toggle();" alt="Inloggen" /></div>
			<div id="login">
				{include file='login.tpl'}
			</div>
			{if !isset($smarty.session.auth_error)}
				<script type="text/javascript">$('#login').hide();</script>
			{/if}
		{/if}
	</div>
</div>

<div id="submenu" onmouseover="ResetTimer();" onmouseout="StartTimer();">
	<div id="submenuitems">
		{foreach name=level1 from=$root->children item=item}
			<div id="sub{$smarty.foreach.level1.iteration}" {if startsWith($path, $item->link)} class="active"{/if}>
				{foreach name=level2 from=$item->children item=subitem}
					<a href="{$subitem->link}" title="{$subitem->tekst}"{if $subitem->active} class="active"{/if}>{$subitem->tekst}</a>
					{if !$smarty.foreach.level2.last}
						<span class="separator">&nbsp;&nbsp;</span>
					{/if}
				{/foreach}
			</div>
		{/foreach}
	</div>
</div>
