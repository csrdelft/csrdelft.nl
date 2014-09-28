<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><div id="beeldmerk"></div></a></div>
	<div id="menucenter">
		<div id="menubanners">
			{assign var=active value=false}
			{foreach from=$root->children item=item name=banner}
				<div id="banner{$smarty.foreach.banner.iteration}" class="menubanner"{if !$active AND $item->active} style="display: block;"{assign var=active value=true}{/if}></div>
			{/foreach}
		</div>
		<ul id="mainmenu">
			{assign var=active value=false}
			{foreach from=$root->children item=item name=main}
				<li>
					<a href="{$item->link}" id="top{$smarty.foreach.main.iteration}" onmouseover="StartShowMenu('{$smarty.foreach.main.iteration}');" onmouseout="ResetShowMenu();"{if !$active AND $item->active} class="active"{/if} title="{$item->tekst}">{$item->tekst}</a>
				</li>
				{if !$active AND $item->active} 
					{assign var=active value=true}
					<script language="javascript" type="text/javascript">
						$(document).ready(function () {
							SetActive({$smarty.foreach.main.iteration});
						});
					</script>
				{/if}
			{/foreach}
		</ul>
	</div>
	<div id="menuright">
		{if LoginModel::mag('P_LOGGED_IN') }
			<div id="ingelogd">
				<div id="uitloggen"><a href="/logout">log&nbsp;uit</a></div>
				{if LoginModel::instance()->isSued()}
					<a href="/endsu/" style="color: red;">{LoginModel::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')} als</a><br />»
				{/if}
				{LoginModel::getUid()|csrnaam}<br />
				<div id="saldi">
					{foreach from=LoginModel::instance()->getLid()->getSaldi() item=saldo}
						<div class="saldoregel">
							<div class="saldo{if $saldo.saldo < 0 AND LoginModel::getUid()!='0524'} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
							{$saldo.naam}:
						</div>
					{/foreach}
				</div>
				{if LoginModel::mag('P_LEDEN_MOD')}
					<div id="adminding">
						Beheer
						{if LoginModel::mag('P_ADMIN')}
							{if $forumcount > 0 OR $queues.meded->count()>0}
								({$forumcount}/{$queues.meded->count()})
							{/if}
						{/if}
						<div>
							{if LoginModel::mag('P_ADMIN')}
								<span class="queues">
									<a href="/forum/wacht">Forum: <span class="count">{$forumcount}</span><br /></a>
										{foreach from=$queues item=queue key=name}
										<a href="/tools/query.php?id={$queue->getID()}">
											{$name|ucfirst}: <span class="count">{$queue->count()}</span><br />
										</a>
									{/foreach}
								</span>
								{if $smarty.const.DEBUG}
									<a href="/su/x101">&raquo; SU Jan Lid.</a><br />
								{/if}
							{/if}
							<a href="/tools/query.php">&raquo; Opgeslagen queries</a><br />
							<a href="/beheer">&raquo; Beheeroverzicht</a><br />
						</div>
					</div>
					{literal}
						<script>
							jQuery(document).ready(function ($) {
								$('#adminding').click(function () {
									$(this).children('div').toggle();
								});
								$('#adminding div').hide();
							});
						</script>
					{/literal}
				{/if}
				<br />
				<form name="lidzoeker" method="get" action="/communicatie/lijst.php">
					<div class="ak" accesskey="a"></div>
					<div class="ak" accesskey="b"></div>
					<div class="ak" accesskey="c"></div>
					<div class="ak" accesskey="d"></div>
					<div class="ak" accesskey="f"></div>
					<div class="ak" accesskey="h"></div>
					<div class="ak" accesskey="i"></div>
					<div class="ak" accesskey="l"></div>
					<div class="ak" accesskey="m"></div>
					<div class="ak" accesskey="p"></div>
					<div class="ak" accesskey="z"></div>
					<input type="text" name="q" id="zoekveld" />
					<script type="text/javascript">
						$(document).ready(function () {
							var instantsearch = {json_encode($instantsearch)};
							$('#zoekveld').click(function (event) {
								this.setSelectionRange(0, this.value.length);
							});
							$('#zoekveld').keyup(function (event) {
								if (event.keyCode === 13 && typeof instantsearch[this.value] !== 'undefined') {
									window.location.href = instantsearch[this.value];
								}
							});
							$('#zoekveld').autocomplete(
						{json_encode(array_keys($instantsearch))},
									{
										clickFire: true,
										max: 20,
										matchContains: true,
										noRecord: ""
									}
							);
							$(document).keydown(function (event) {
								// Geen instantsearch met modifiers
								if (bShiftPressed || bCtrlPressed || bMetaPressed) {
									return;
								}
								// Geen instantsearch als we in een input-element of text-area zitten.
								var element = event.target.tagName.toUpperCase();
								if (element == 'INPUT' || element == 'TEXTAREA' || element == 'SELECT') {
									return;
								}
								// Sneltoetsen
								if (bAltPressed) {
									switch (event.charCode) {
										case 97: //a voor fotoalbum
											location.href = "{$smarty.const.CSR_ROOT}/fotoalbum";
											break;
										case 98: //b voor besturen
											location.href = "{$smarty.const.CSR_ROOT}/actueel/groepen/Besturen";
											break;
										case 99: //c voor courant
											location.href = "{$smarty.const.CSR_ROOT}/actueel/courant";
											break;
										case 100: //d voor documenten
											location.href = "{$smarty.const.CSR_ROOT}/communicatie/documenten";
											break;
										case 102: //f voor forum
											location.href = "{$smarty.const.CSR_ROOT}/forum/recent";
											break;
										case 104: //h voor thuis
											location.href = "{$smarty.const.CSR_ROOT}/";
											break;
										case 105: //i voor instellingen
											location.href = "{$smarty.const.CSR_ROOT}/instellingen";
											break;
										case 108: //l voor ledenlijst
											location.href = "{$smarty.const.CSR_ROOT}communicatie/ledenlijst";
											break;
										case 109: //m voor mededelingen
											location.href = "{$smarty.const.CSR_ROOT}/actueel/mededelingen";
											break;
										case 112: //p voor profiel
											location.href = "{$smarty.const.CSR_ROOT}/communicatie/profiel.php";
											break;
										case 122: //z voor focus naar het ledenzoekveldje.
											jQuery('#zoekveld').focus();
											break;
									}
								}
								else if (event.keyCode > 64 && event.keyCode < 91) {
									$('#zoekveld').focus();
								}
							});
						});
					</script>
				</form>
			</div>
		{/if}
	</div>
</div>
<div id="submenu" onmouseover="ResetTimer();" onmouseout="StartTimer();">
	<div id="submenuitems">
		{assign var=active value=false}
		{foreach name=level1 from=$root->children item=item}
			<div id="sub{$smarty.foreach.level1.iteration}"{if !$active AND $item->active} class="active"{assign var=active value=true}{/if}>
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