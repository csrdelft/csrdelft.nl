<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/vb"><img src="images/logo_vb.png" alt="Logo" /></a></div>
	<div id="menucenter">
		<div id="menubanner">
			<div><img src="images/vormingsbank_vb.png" id="vblogobanner" alt="banner1"  /></div>
		</div>
		<div id="mainmenu">
			<ul>
			{foreach from=$items item=item}
				<li><a href="{$item.link}" id="top{$item.ID}" onmouseover="StartShowMenu('{$item.ID}');" onmouseout="ResetShowMenu();" {if $item.huidig}class="active" {/if}title="{$item.tekst}">{$item.tekst}</a></li>
					{if $item.huidig}
						<script type="text/javascript">
							SetActive({$item.ID});
						</script>
					{/if}
				{/foreach}
			</ul>
		</div>
	</div>
	<div id="menuright">
		{if $lid->hasPermission('P_LOGGED_IN') }
			<div id="ingelogd">
				{$lid->getCivitasName()}<br />
				<div id="profiellink"><a href="/communicatie/profiel/{$lid->getUid()}">profiel</a></div> <div id="uitloggen"><a href="/logout.php">log&nbsp;uit</a></div><br class="clear" />
				<br/>
				<form method="get" action="/vb/ajaxsearch.php">
					<p>
						<input type="hidden" name="actie" value="search"/>
						<input type="hidden" name="mode" value="simple"/>
						<input type="text" id="zoekveld" name="q" value="zoekterm" onfocus="this.value=''; this.style.textAlign='left';"/>
					</p>
				</form>
			</div>
		{else}
			<div id="key"><img src="images/key_vb.png" alt="Inloggen" onclick="ToggleLogin();" /></div>
			<div id="login">			
				{if isset($smarty.session.auth_error)}
					<span class="waarschuwing">{$smarty.session.auth_error}</span>
				<script type="text/javascript">
						<!--
						document.getElementById('login').style.display='block';
						-->
					</script>
				{/if}
				<form action="/login.php" method="post">
					<fieldset>
						<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
						<input type="text" name="user" value="naam" onfocus="if(this.value=='naam')this.value='';" />
						<input type="password" name="pass" value="wachtwoord" onfocus="if(this.value=='wachtwoord')this.value='';" />
						<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" checked="checked" />
						<label for="login-checkip">Koppel IP</label><br /><br /><br />
						<input type="submit" name="submit" value="Inloggen" />
					</fieldset>
				</form>			
			</div>
		{/if}
	</div>
</div>

<div id="submenu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="submenuitems">
		{foreach from=$items item=item}
			<div id="sub{$item.ID}"{if $item.huidig} class="active"{/if}>
				{assign var='showseperator' value=false}
				{foreach from=$item.subitems item=subitem}
					{if $showseperator} <img src="http://plaetjes.csrdelft.nl/worst/submenuseperator.png" alt="|" /> {/if}
					{assign var='showseperator' value=true}
					<a href="{$subitem.link}" title="{$subitem.tekst}"{if $subitem.huidig} class="active"{/if}>{$subitem.tekst}</a>
				{/foreach}
			</div>						
		{/foreach}
	</div>
</div>