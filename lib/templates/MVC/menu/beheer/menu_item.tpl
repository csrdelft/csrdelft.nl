{*
	menu_item.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<li id="menu-item-{$item->item_id}" parentid="{$item->parent_id}" class="menu-item"{if !$item->item_id} style="list-style-type: none; background: none;"{/if}>
	<div class="inline-edit-{$item->item_id}" style="text-align: left;">
		<div style="display: inline-block; width: 25px;">
{if $item->item_id}
			<a title="Item wijzigen" class="knop" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();">{icon get="pencil"}</a>
{/if}
		</div>
		<div style="display: inline-block; width: 40px;">
			<a title="Nieuw sub-item" class="knop" onclick="menubeheer_clone({$item->item_id});">{icon get="add"}</a>
		</div>
		<div style="display: inline-block; width: 50px; color: grey;">
			{$item->item_id}
		</div>
		<div style="display: inline-block; width: 160px;{if $item->children} font-weight: bold;{/if}">
			{$item->tekst}
		</div>
		<div style="display: inline-block; width: 275px;">
			<a href="{$item->link}">{$item->link}</a>
		</div>
{if $item->item_id}
		<div style="display: inline-block; width: 25px;">
			<form method="post" action="/menubeheer/wijzig/{$item->item_id}/zichtbaar">
				<input type="hidden" name="zichtbaar" value="{if $item->zichtbaar}0{else}1{/if}" />
				<input type="image" src="{$CSR_PICS}/famfamfam/{if $item->zichtbaar}eye{else}shading{/if}.png" title="{if $item->zichtbaar}Menu-item is nu zichtbaar.&#013;Klik om onzichtbaar te maken.{else}Menu-item is nu onzichtbaar.&#013;Klik om zichtbaar te maken{/if}" />
			</form>
		</div>
		<div style="display: inline-block; width: 60px; text-align: center;">
			{$item->prioriteit}
		</div>
		<div style="display: inline-block; width: 25px;">
			<a href="/menubeheer/verwijder/{$item->item_id}" title="Menu-item definitief verwijderen" class="knop confirm">{icon get="cross"}</a>
		</div>
{/if}
	</div>
{if $item->item_id}
	<div class="inline-edit-{$item->item_id}" style="display: none;">
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/parent_id">
			<div style="display: inline-block; width: 75px;">Parent id:</div>
			<input type="text" name="parent_id" maxlength="5" size="60" value="{$item->parent_id}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/prioriteit">
			<div style="display: inline-block; width: 75px;">Prioriteit:</div>
			<input type="text" name="prioriteit" maxlength="5" size="60" value="{$item->prioriteit}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/tekst">
			<div style="display: inline-block; width: 75px;">Label:</div>
			<input type="text" name="tekst" maxlength="255" size="60" value="{$item->tekst}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/link">
			<div style="display: inline-block; width: 75px;">Url:</div>
			<input type="text" name="link" maxlength="255" size="60" value="{$item->link}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/permission">
			<div style="display: inline-block; width: 75px;">Rechten:</div>
			<input type="text" name="permission" maxlength="255" size="60" value="{$item->permission}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
		<form method="post" action="/menubeheer/wijzig/{$item->item_id}/menu">
			<div style="display: inline-block; width: 75px;">Menu:</div>
			<input type="text" name="menu" maxlength="255" size="60" value="{$item->menu_naam}" />
			&nbsp;<input type="submit" value="opslaan" />
			&nbsp;<input type="reset" value="annuleren" onclick="$('.inline-edit-{$item->item_id}').slideDown();$(this).parent().parent().slideUp();" />
		</form>
	</div>
{/if}
	<ul id="children-{$item->item_id}">
		<li id="inline-newchild-{$item->item_id}" style="display: none;">
			<form method="post" action="/menubeheer/nieuw/{$item->item_id}">
				<div style="display: inline-block; width: 75px;">Parent id:</div>
				<input type="text" name="parent_id" maxlength="5" size="60" value="{$item->item_id}" /><br />
				<div style="display: inline-block; width: 75px;">Prioriteit:</div>
				<input type="text" name="prioriteit" maxlength="5" size="60" value="0" /><br />
				<div style="display: inline-block; width: 75px;">Label:</div>
				<input type="text" name="tekst" maxlength="255" size="60" value="Tekst" /><br />
				<div style="display: inline-block; width: 75px;">Url:</div>
				<input type="text" name="link" maxlength="255" size="60" value="/url" /><br />
				<div style="display: inline-block; width: 75px;">Rechten:</div>
				<input type="text" name="permission" maxlength="255" size="60" value="P_NOBODY" /><br />
				<div style="display: inline-block; width: 75px;">&nbsp</div>
				<input type="hidden" name="menu_naam" value="{$item->menu_naam}" />
				<input type="submit" value="opslaan" />&nbsp;
				<input type="reset" value="annuleren" onclick="$(this).parent().parent().slideUp(400, function() {ldelim} $(this).remove(); {rdelim});" />
			</form>
		</li>
	{foreach from=$item->children item=child}
		{include file='MVC/menu/beheer/menu_item.tpl' item=$child}
	{/foreach}
	</ul>
	{if $item->children}
	<hr />
	{/if}
</li>