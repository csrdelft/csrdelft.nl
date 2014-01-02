{*
	menu-item.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div id="menu-item-{$item->getMenuId()}">
	<p>
		{$item->getMenuId()}
		(<div class="menu-prio" onclick="$('.menu-prio').toggle();" style="display: inline;">{$item->getPrioriteit()}</div>):
		<form method="post" action="/menubeheer/wijzig/{$item->getMenuId()}/prioriteit" style="display: inline;">
			<input type="text" name="Prioriteit" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="$('.menu-prio').toggle();" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
		
	
	{$item->getTekst()} ({$item->getLink()})
	
	
		<a href="/menubeheer/verwijder/{$item->getMenuId()}" title="Functie wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</p>
{foreach from=$item->getChildren() item=child}
	{include file='menu/beheer/menu-item.tpl' item=$child}
{/foreach}
</div>


