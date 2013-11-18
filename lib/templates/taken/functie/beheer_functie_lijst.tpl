{*
	beheer_functie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveefunctie-row-{$functie->getFunctieId()}">
	<td>
		<a href="{$module}/bewerk/{$functie->getFunctieId()}" title="Functie wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</td>
	<td>{$functie->getAfkorting()}</td>
	<td>{$functie->getNaam()}</td>
	<td>{$functie->getStandaardPunten()}</td>
	<td title="{$functie->getEmailBericht()}">{if strlen($functie->getEmailBericht()) > 0}{icon get="email"}{/if}</td>
	<td>
		{if $functie->getIsKwalificatieBenodigd()}<a href="{$module}/kwalificeer/{$functie->getFunctieId()}" title="Kwalificatie toewijzen" class="knop post popup">{icon get="vcard_add"} Kwalificeer</a>{/if}
		{foreach from=$functie->getGekwalificeerden() item=kwali}
			<div>
				<a href="{$module}/dekwalificeer/{$functie->getFunctieId()}" title="Kwalificatie intrekken" class="knop post" post="voor_lid={$kwali->getLidId()}">{icon get="vcard_delete"}</a>
				&nbsp;{$kwali->getLid()->getNaamLink('civitas', 'link')}
			</div>
		{/foreach}
	</td>
	<td style="text-align:center;">
		<a href="{$module}/verwijder/{$functie->getFunctieId()}" title="Functie definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
	</td>
</tr>