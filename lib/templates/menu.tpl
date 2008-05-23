<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><img src="http://plaetjes.csrdelft.nl/worst/logo.png" alt="Logo" /></a></div>
	<div id="menucenter">
		<div id="menubanner">
			<div id="banner1"><img src="http://plaetjes.csrdelft.nl/menubalk/banner1.png" id="imgbanner1" alt="banner1" width="553" height="106" /></div>
			<div id="banner2"><img src="http://plaetjes.csrdelft.nl/menubalk/banner2.png" id="imgbanner2" alt="banner2" width="553" height="106" /></div>
			<div id="banner3"><img src="http://plaetjes.csrdelft.nl/menubalk/banner3.png" id="imgbanner3" alt="banner3" width="553" height="106" /></div>
			<div id="banner4"><img src="http://plaetjes.csrdelft.nl/menubalk/banner4.png" id="imgbanner4" alt="banner4" width="553" height="106" /></div>
		</div>
		<div id="mainmenu">
			<ul>
			{foreach from=$items item=item}
				<li><a href="{$item.link}" id="top{$item.ID}" onmouseover="StartShowMenu('{$item.ID}');" onmouseout="ResetShowMenu();" {if $item.huidig}class="active" {/if}title="{$item.tekst}">{$item.tekst}</a></li>
					{if $item.huidig}
						<script type="text/javascript">
							SetActive({$item.ID});
							document.getElementById('banner'+{$item.ID}).style.display = "inline";
							fixPNG('imgbanner1')
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
				<br />
				<table id="saldi">
					<tr><th> </th><th class="boven">Saldo</th></tr>
					{foreach from=$lid->getSaldi() item=saldo}
						<tr><th>{$saldo.naam}</th><td{if $saldo.saldo < 0} style="color: red;"{/if}>&euro; {$saldo.saldo}</td></tr>
					{/foreach}
				</table>
				<br />
				<form method="post" action="/communicatie/lijst.php">
					<p>
						<input type="hidden" name="a" value="zoek" />
						<input type="hidden" name="waar" value="naam" />
						<input type="hidden" name="moot" value="alle" />
						<input type="hidden" name="status" value="leden" />
						<input type="hidden" name="sort" value="achternaam" />
						<input type="hidden" name="kolom[]" value="adres" />
						<input type="hidden" name="kolom[]" value="email" />
						<input type="hidden" name="kolom[]" value="telefoon" />
						<input type="hidden" name="kolom[]" value="mobiel" />
						{if isset($smarty.post.wat)}
							<input type="text" value="{$smarty.post.wat|escape:'htmlall'}" name="wat" id="zoekveld" />
						{else}
							<input type="text" value="naam zoeken" onfocus="this.value=''; this.style.textAlign='left';" name="wat" id="zoekveld" />
						{/if}
					</p>
				</form>
			</div>
		{else}
			<div id="key"><img src="http://plaetjes.csrdelft.nl/worst/key.gif" alt="Inloggen" onclick="ToggleLogin();" /></div>
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