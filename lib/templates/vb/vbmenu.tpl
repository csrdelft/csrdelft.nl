<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><img src="images/logo_vb.png" alt="Logo" /></a></div>
	<div id="menucenter">
		<div id="menubanner">
			<div><img src="images/vormingsbank_vb_beta.png" id="vblogobanner" alt="banner1"  /></div>
		</div>
		<ul id="mainmenu">
			{foreach from=$items item=item}
				<li>
					<a href="{$item.link}" id="top{$item.ID}" onmouseover="StartShowMenu('{$item.ID}');" onmouseout="ResetShowMenu();" {if $item.huidig}class="active" {/if}title="{$item.tekst}">{$item.tekst}</a>
					{if $item.huidig}
						<script type="text/javascript">
							SetActive({$item.ID});
							document.getElementById('banner'+{$item.ID}).style.display = "inline";
							fixPNG('vblogobanner')
						</script>
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
	<div id="menuright">
		{if $loginlid->hasPermission('P_LOGGED_IN') }
			<div id="ingelogd">
				{if $loginlid->isSued()}
					<a href="/endsu/" style="color: red;">{$loginlid->getSuedFrom()->getNaamLink('civitas','html')} als</a><br />» 
				{/if}
				{$loginlid->getUid()|csrnaam}<br />				
				<div id="uitloggen"><a href="/logout.php">log&nbsp;uit</a></div><br class="clear" />
				<br />
<!--				
				<form method="get" action="/vb/ajaxsearch.php">
					<p>
						<input type="hidden" name="actie" value="search"/>
						<input type="hidden" name="mode" value="simple"/>
						<input type="text" id="zoekveld" name="q" value="zoekterm" onfocus="this.value=''; this.style.textAlign='left';"/>
					</p>
				</form>
-->
			</div>
		{else}
			<div id="key"><img src="images/key_vb.png" onclick="toggleDiv('login')" alt="Inloggen" /></div>
			<div id="login">			
				{if isset($smarty.session.auth_error)}
					<span class="waarschuwing">{$smarty.session.auth_error}</span>
				{/if}
				<form action="/login.php" method="post">
					<fieldset>
						<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
						<input type="text" name="user" value="naam" onfocus="if(this.value=='naam')this.value='';" />
						<input type="password" name="pass" value="" />
						<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" checked="checked" />
						<label for="login-checkip">Koppel IP</label>
						<input type="submit" class="submit" name="submit" value="Inloggen" />
					</fieldset>
				</form>			
			</div>
			{if !isset($smarty.session.auth_error)}
				<script type="text/javascript">hideDiv(document.getElementById('login'));</script>
			{/if}			
		{/if}
	</div>
</div>

<div id="submenu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="submenuitems">
		{foreach from=$items item=item}
			<div id="sub{$item.ID}"{if $item.huidig} class="active"{/if}>
				{assign var='showseperator' value=false}
				{foreach from=$item.subitems item=subitem}
					{if $showseperator} <img src="http://plaetjes.csrdelft.nl/layout/submenuseperator.gif" alt="|" /> {/if}
					{assign var='showseperator' value=true}
					<a href="{$subitem.link}" title="{$subitem.tekst}"{if $subitem.huidig} class="active"{/if}>{$subitem.tekst}</a>
				{/foreach}
			</div>						
		{/foreach}
	</div>
</div>
