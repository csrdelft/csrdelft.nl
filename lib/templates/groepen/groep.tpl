{$melding}
<ul class="horizontal">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groep->getTypeId()}<strong>{/if}
			<a href="/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groep->getTypeId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
<div class="groepleden">
	<table>
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				<td><em>{$groeplid.functie|escape:'html'}</em></td>
				{if $groep->magBewerken()}
					<td><a href="/groepen/{$gtype}/{$groep->getId()}/verwijderLid/{$groeplid.uid}">X</a></td>
				{/if}
			</tr>
		{/foreach}
	</table>
	{if $groep->magBewerken() AND $action!='edit'}
		<form action="/groepen/{$gtype}/{$groep->getId()}/addLid" method="post">
			{if $action=='addLid' AND $lidAdder!=false}
				{$lidAdder}
			{else}
				<input type="text" name="rawNamen" /> 
			{/if}
			<input type="submit" value="toevoegen" />
		</form>
	{/if}
</div>

<h2>{$groep->getNaam()}</h2>
{if $groep->magBewerken() AND $action=='edit'}
	<form action="/groepen/{$gtype}/{$groep->getId()}/bewerken" method="post">
	<div class="groepAdmin" style="width: 100%; clear: both;">
		{if $groep->isAdmin()}
			{if $groep->getId()==0}
				<strong>Korte naam:</strong> (Voor in urls. Alleen letters, geen spaties. Voor elkaar opvolgende groepen dezelfde naam gebruiken;)<br />
				<input type="text" name="snaam" style="width: 100%" value="{$groep->getSnaam()|escape:'html'}" />
			{/if}
			<strong>Naam:</strong><br />
			<input type="text" name="naam" style="width: 100%" value="{$groep->getNaam()|escape:'html'}" />
			<strong>Status:</strong> <select name="status">
			<option value="ht" {if $groep->getStatus()=="ht"}selected="selected"{/if}>h.t.</option>
			<option value="ot" {if $groep->getStatus()=="ot"}selected="selected"{/if}>o.t.</option>
			<option value="ft" {if $groep->getStatus()=="ft"}selected="selected"{/if}>f.t.</option>
			</select>
			<strong>Installatiedatum:</strong> <input type="text" name="installatie" value="{$groep->getInstallatie()}" />
			<br /><br />
			<strong>Korte beschrijving:</strong><br />
			<textarea name="sbeschrijving" style="width: 100%; height: 100px;">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		{/if}
		<strong>Lange beschrijving:</strong><br />
		<textarea name="beschrijving" style="width: 100%; height: 200px;">{$groep->getBeschrijving()|escape:'html'}</textarea>
		<input type="submit" value="Opslaan" /> <a href="/groepen/{$gtype}/{$groep->getId()}/" class="knop">terug</a>
	</div>
	</form>
{else}
	{$groep->getSbeschrijving()|ubb}
	<div class="clear" id="voorgangerOpvolger">
		{if is_array($opvolgerVoorganger)}
			<ul>
			{if isset($opvolgerVoorganger.opvolger)}
				<li style="list-style-image: url({$csr_pics}groepen/up.png)"><a href="/groepen/{$gtype}/{$opvolgerVoorganger.opvolger->getId()}/">{$opvolgerVoorganger.opvolger->getNaam()}</a></li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger) OR isset($opvolgerVoorganger.opvolger)}
				<li style="list-style-type: none">{$groep->getNaam()}</li>
			{/if}
			{if isset($opvolgerVoorganger.voorganger)}
				<li style="list-style-image: url({$csr_pics}groepen/down.png)"><a href="/groepen/{$gtype}/{$opvolgerVoorganger.voorganger->getId()}/">{$opvolgerVoorganger.voorganger->getNaam()}</a></li>
			{/if}
			</ul>
		{/if}
		{if $groep->isAdmin()}
			<br /><a href="/groepen/{$gtype}/0/bewerken/{$groep->getSnaam()}/">&raquo; Opvolger toevoegen</a><br />
		{/if}
	</div> 
	{if $groep->magBewerken()}
		<div style="float: right; margin: 10px ;"><a href="/groepen/{$gtype}/{$groep->getId()}/bewerken" class="knop"><img src="{$csr_pics}forum/bewerken.png" title="Bewerk groep" /></a></div>
	{/if}
	
	{$groep->getBeschrijving()|ubb}
{/if}
<div class="clear"></div>