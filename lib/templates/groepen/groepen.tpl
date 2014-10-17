{getMelding()}
<ul class="horizontal">
	{foreach from=$groeptypes item=groeptype}
		<li{if $groeptype.id==$groepen->getId()} class="active"{/if}>
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		</li>
	{/foreach}
</ul>
<hr />
{if !$groepen->getToonHistorie()}
	<div id="groepLijst">
		<ul>
			{foreach from=$groepen->getGroepen() item=groep name=g}
				<li><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
				{/foreach}	
		</ul>
	</div>
{/if}
{if $action=='edit'}
	<h1>{$groepen->getNaam()}</h1>
	<form action="/actueel/groepen/{$groepen->getNaam()}/?bewerken=true" method="post">
		<div id="groepenFormulier" class="groepFormulier">
			<div id="bewerkPreview" class="preview"></div>
			<label for="beschrijving"><strong>Beschrijving:</strong><br /><br />bbcode mogelijk</label>
			<textarea id="typeBeschrijving" name="beschrijving" style="width:444px;" rows="15">{$groepen->getBeschrijving()|escape:'html'}</textarea><br />
			<label for="submit"></label><input type="submit" id="submit" value="Opslaan" /> <input type="button" value="Voorbeeld" onclick="return CsrBBPreview('typeBeschrijving', 'bewerkPreview')" /> <a href="/actueel/groepen/{$groepen->getNaam()}/" class="knop">Terug</a>
			<a class="knop float-right opmaakhulp" title="Opmaakhulp weergeven">Opmaak</a>
			<a class="knop float-right vergroot" data-vergroot="#typeBeschrijving" title="Vergroot het invoerveld">&uarr;&darr;</a>
			<hr />
		</div>
	</form>
{else}
	{$groepen->getBeschrijving()|bbcode}
{/if}
<div class="clear">
	{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe {$groepen->getNaamEnkelvoud()}</a>
	{/if}	
	{if $groepen->isAdmin()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/?maakOt=true" class="knop" 
		   onclick="return confirm('Weet u zeker dat alle h.t. groepen in deze categorie o.t. moeten worden?')">
			Maak h.t. groepen o.t.
		</a>
	{/if}
	{if LoginModel::mag('P_ADMIN') AND $action!='edit'}
		<a class="knop" href="/actueel/groepen/{$groepen->getNaam()}/?bewerken=true">
			<img src="{$CSR_PICS}/famfamfam/pencil.png" title="Bewerk beschrijving" />
		</a>
	{/if}
</div>

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep clear" id="groep{$groep->getId()}">
		<div class="groepleden">
			{if $groep->toonPasfotos()}
				{assign var='actie' value='pasfotos'}
			{/if}
			{include file='groepen/groepleden.tpl'}
		</div>
		<h2><a href="/actueel/groepen/{$groepen->getNaam()}/{$groep->getId()}/">{$groep->getNaam()}</a></h2>
		{if $groep->getType()->getId()==11 }Ouderejaars: {$groep->getEigenaar()}<br /><br />{/if} {* alleen bij Sjaarsacties *}
			{$groep->getSbeschrijving()|bbcode}
		</div>
		{/foreach}
			<hr class="clear" />
			{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
				<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe {$groepen->getNaamEnkelvoud()}</a>
			{/if}

