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
			{foreach from=$root->children item=item}
				<li>
					<a href="{$item->link}" id="top{$item->item_id}" onmouseover="StartShowMenu('{$item->item_id}');" onmouseout="ResetShowMenu();"{if $item->isParentOf($huidig)} class="active"{/if} title="{$item->tekst}">{$item->tekst}</a>
					{if $item->isParentOf($huidig)}
						<script type="text/javascript">
							SetActive({$item->item_id});
							document.getElementById('banner'+{$item->item_id}).style.display = "inline";
							fixPNG('imgbanner1');
						</script>
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
	<div id="menuright">
		{if $loginlid->hasPermission('P_LOGGED_IN') }
			<div id="ingelogd">
				<a href="/instellingen/" class="instellingen" title="Webstekinstellingen">{icon get="instellingen"}</a>
				{if $loginlid->isSued()}
					<a href="/endsu/" style="color: red;">{$loginlid->getSuedFrom()->getNaamLink('civitas', 'plain')} als</a><br />»
				{/if}
				{$loginlid->getUid()|csrnaam}<br />
				<div id="uitloggen"><a href="/logout.php">log&nbsp;uit</a></div>
				<div id="saldi">
					{foreach from=$loginlid->getLid()->getSaldi() item=saldo}
						<div class="saldoregel">
							<div class="saldo{if $saldo.saldo < 0 AND $loginlid->getUid()!='0524'} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
							{$saldo.naam}:
						</div>
					{/foreach}
				</div>
				{if $loginlid->hasPermission('P_LEDEN_MOD')}
				<div id="adminding">
					Beheer
					{if $loginlid->hasPermission('P_ADMIN')}
						{if $queues.forum->count()>0 OR $queues.meded->count()>0}
							({$queues.forum->count()}/{$queues.meded->count()})
						{/if}
					{/if}
					<div>
						{if $loginlid->hasPermission('P_ADMIN')}
						<span class="queues">
							{foreach from=$queues item=queue key=name}
								<a href="/tools/query.php?id={$queue->getID()}">
									{$name|ucfirst}: <span class="count">{$queue->count()}</span><br />
								</a>
							{/foreach}
						</span>
						<a href="/su/x101">&raquo; SU Jan Lid.</a><br />
						{/if}
						<a href="/pagina/beheer">&raquo; Beheeroverzicht</a><br />
						<a href="/tools/query.php">&raquo; Opgeslagen queries</a><br />
						<a href="/menubeheer">&raquo; Menubeheer</a> <a href="/instellingenbeheer">&raquo; Instellingen</a><br />
					</div>
				</div>
				{literal}
				<script>
					jQuery(document).ready(function($){
						$('#adminding').click(function(){
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
				{if isset($smarty.session.auth_error)}
					<span class="waarschuwing">{$smarty.session.auth_error}</span>
				{/if}
				<form action="/login.php" method="post">
					<fieldset>
						<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
						<input type="text" name="user" value="naam" onfocus="if(this.value==='naam')this.value='';" />
						<input type="password" name="pass" value="wachtwoord" />
						<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" />
						<label for="login-checkip">Koppel IP</label>
						<input type="submit" class="submit" name="submit" value="Inloggen" />
					</fieldset>
				</form>
			</div>
			{if !isset($smarty.session.auth_error)}
				<script type="text/javascript">$('#login').hide();</script>
			{/if}
		{/if}
	</div>
</div>

<div id="submenu" onmouseover="ResetTimer();" onmouseout="StartTimer();">
	<div id="submenuitems">
{foreach from=$root->children item=item}
	{foreach name=sub from=$item->children item=subitem}
		{if $smarty.foreach.sub.first}
			<div id="sub{$item->item_id}"{if $item->isParentOf($huidig)} class="active"{/if}>
		{/if}
			<a href="{$subitem->link}" title="{$subitem->tekst}"{if $subitem === $huidig} class="active"{/if}>{$subitem->tekst}</a>
		{if !$smarty.foreach.sub.last}
			<span class="separator">&nbsp;&nbsp;</span>
		{else}
			</div>
		{/if}
	{/foreach}
{/foreach}
	</div>
</div>
