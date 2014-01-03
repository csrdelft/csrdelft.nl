{*
	menu_item.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<li id="menu-item-{$item->getMenuId()}" parentid="{$item->getParentId()}" class="menu-item"{if $item->getMenuId() === 0} style="list-style-type: none; background: none;"{/if}>
	<div class="inline-edit-{$item->getMenuId()}">
		<div style="display: inline-block; width: 25px;">
{if $item->getMenuId() !== 0}
			<a title="Item wijzigen" class="knop" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();">{icon get="pencil"}</a>
{/if}
		</div>
		<div style="display: inline-block; width: 40px;">
			<a title="Nieuw sub-item" class="knop" onclick="menubeheer_clone({$item->getMenuId()});">{icon get="add"}</a>
		</div>
		<div style="display: inline-block; width: 50px; color: grey;">
			{$item->getMenuId()}
		</div>
		<div style="display: inline-block; width: 170px;{if $item->children} font-weight: bold;{/if}">
			{$item->getTekst()}
		</div>
		<div style="display: inline-block; width: 310px;">
			<a href="{$item->getLink()}">{$item->getLink()}</a>
		</div>
{if $item->getMenuId() !== 0}
		<div style="display: inline-block; width: 25px;">
			<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/zichtbaar">
				<input type="hidden" name="Zichtbaar" value="{if $item->getIsZichtbaar()}0{else}1{/if}" />
				<input type="image" src="{$csr_pics}/famfamfam/{if $item->getIsZichtbaar()}eye{else}shading{/if}.png" />
			</form>
		</div>
		<div style="display: inline-block; width: 60px; text-align: center;">
			{$item->getPrioriteit()}
		</div>
		<div style="display: inline-block; width: 25px;">
			<a href="/menubeheer/verwijder/{$item->getMenuId()}" title="Menu-item definitief verwijderen" class="knop confirm">{icon get="cross"}</a>
		</div>
{/if}
	</div>
{if $item->getMenuId() !== 0}
	<div class="inline-edit-{$item->getMenuId()}" style="display: none;">
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/parentId">
			<div style="display: inline-block; width: 75px;">Parent id:</div>
			<input type="text" name="ParentId" maxlength="5" size="60" value="{$item->getParentId()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/prioriteit">
			<div style="display: inline-block; width: 75px;">Prioriteit:</div>
			<input type="text" name="Prioriteit" maxlength="5" size="60" value="{$item->getPrioriteit()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/tekst">
			<div style="display: inline-block; width: 75px;">Label:</div>
			<input type="text" name="Tekst" maxlength="255" size="60" value="{$item->getTekst()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/link">
			<div style="display: inline-block; width: 75px;">Url:</div>
			<input type="text" name="Link" maxlength="255" size="60" value="{$item->getLink()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/permission">
			<div style="display: inline-block; width: 75px;">Rechten:</div>
			<input type="text" name="Permission" maxlength="255" size="60" value="{$item->getPermission()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/menu">
			<div style="display: inline-block; width: 75px;">Menu:</div>
			<input type="text" name="Menu" maxlength="255" size="60" value="{$item->getMenu()}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->getMenuId()}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
	</div>
{/if}
	<ul id="children-{$item->getMenuId()}">
		<li id="inline-newchild-{$item->getMenuId()}" style="display: none;">
			<form method="post" action="/menubeheer/nieuw/{$item->getMenuId()}">
				<div style="display: inline-block; width: 75px;">Parent id:</div>
				<input type="text" name="ParentId" maxlength="5" size="60" value="{$item->getMenuId()}" /><br />
				<div style="display: inline-block; width: 75px;">Prioriteit:</div>
				<input type="text" name="Prioriteit" maxlength="5" size="60" value="0" /><br />
				<div style="display: inline-block; width: 75px;">Label:</div>
				<input type="text" name="Tekst" maxlength="255" size="60" value="Tekst" /><br />
				<div style="display: inline-block; width: 75px;">Url:</div>
				<input type="text" name="Link" maxlength="255" size="60" value="/url" /><br />
				<div style="display: inline-block; width: 75px;">Rechten:</div>
				<input type="text" name="Permission" maxlength="255" size="60" value="P_NOBODY" /><br />
				<div style="display: inline-block; width: 75px;">&nbsp</div>
				<input type="hidden" name="Menu" value="{$item->getMenu()}" />
				<input type="submit" value="opslaan" />&nbsp;
				<input type="reset" value="annuleren" onclick="$(this).parent().parent().slideUp(400, function() {ldelim} $(this).remove(); {rdelim});" />
			</form>
		</li>
	{foreach from=$item->children item=child}
		{include file='menu/beheer/menu_item.tpl' item=$child}
	{/foreach}
	</ul>
	{if $item->children}
	<hr />
	{/if}
</li>
{/strip}
