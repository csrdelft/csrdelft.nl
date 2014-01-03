{*
	menu_new.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
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
		<input type="button" value="opslaan" onclick="menubeheer_submit($(this).parent());" />&nbsp;
		<input type="reset" value="annuleren" onclick="$(this).parent().parent().remove();" />
	</form>
</li>
{/strip}