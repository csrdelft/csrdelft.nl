<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><img src="http://plaetjes.csrdelft.nl/owee/2009/logo.png" alt="Logo" id="logo" /></a></div>
	<div id="menucenter">
		<div id="menubanner">
			<div id="banner0" style="margin-left: 95px"><img src="http://plaetjes.csrdelft.nl/menubalk/owee_most_wanted.png" id="imgbanner0" alt="banner0" width="377" height="106" /></div>
			<div id="banner1"><img src="http://plaetjes.csrdelft.nl/menubalk/banner1.png" id="imgbanner1" alt="banner1" width="553" height="106" /></div>
			<div id="banner2"><img src="http://plaetjes.csrdelft.nl/menubalk/banner2.png" id="imgbanner2" alt="banner2" width="553" height="106" /></div>
			<div id="banner3"><img src="http://plaetjes.csrdelft.nl/menubalk/banner3.png" id="imgbanner3" alt="banner3" width="553" height="106" /></div>
			<div id="banner4"><img src="http://plaetjes.csrdelft.nl/menubalk/banner4.png" id="imgbanner4" alt="banner4" width="553" height="106" /></div>
		</div>
		<ul id="mainmenu">
			{foreach from=$items item=item}
				<li>
					<a href="{$item.link}" id="top{$item.ID}" onmouseover="StartShowMenu('{$item.ID}');" onmouseout="ResetShowMenu();" {if $item.huidig}class="active" {/if}title="{$item.tekst}">{$item.tekst}</a>
					{if $item.huidig}
						<script type="text/javascript">
							SetActive({$item.ID});
							document.getElementById('banner'+{$item.ID}).style.display = "inline";
							fixPNG('imgbanner1')
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
				<div id="saldi">
					{foreach from=$loginlid->getLid()->getSaldi() item=saldo}
						<div class="saldoregel">
							<div class="saldo{if $saldo.saldo < 0} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
							{$saldo.naam}:
						</div>
					{/foreach}
				</div>
				<br />
				<form method="post" action="/communicatie/lijst.php" name="lidzoeker">
					<p>
						<input type="hidden" name="a" value="zoek" />
						<input type="hidden" name="waar" value="naam" />
						<input type="hidden" name="moot" value="alle" />
						<input type="hidden" name="status" value="(oud)?leden" />
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
			<div id="key"><img src="http://plaetjes.csrdelft.nl/owee/2009/key.gif" onclick="toggleDiv('login')" alt="Inloggen" /></div>
			<div id="login">			
				{if isset($smarty.session.auth_error)}
					<span class="waarschuwing">{$smarty.session.auth_error}</span>
				{/if}
				<form action="/login.php" method="post">
					<fieldset>
						<input type="hidden" name="url" value="{$smarty.server.REQUEST_URI}" />
						<input type="text" name="user" value="naam" onfocus="if(this.value=='naam')this.value='';" />
						<input type="password" name="pass" value="" />
						<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" />
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
					{if $showseperator} <img src="http://plaetjes.csrdelft.nl/owee/2009/submenuseperator.gif" alt="|" /> {/if}
					{assign var='showseperator' value=true}
					<a href="{$subitem.link}" title="{$subitem.tekst}"{if $subitem.huidig} class="active"{/if}>{$subitem.tekst}</a>
				{/foreach}
			</div>						
		{/foreach}
	</div>
</div>
