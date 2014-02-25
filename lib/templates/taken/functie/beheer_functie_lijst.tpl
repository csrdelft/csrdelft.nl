{*
	beheer_functie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveefunctie-row-{$functie->getFunctieId()}">
	<td>
		<a href="{$instellingen->get('taken', 'url')}/bewerk/{$functie->getFunctieId()}" title="Functie wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</td>
	<td>{$functie->getAfkorting()}</td>
	<td>{$functie->getNaam()}</td>
	<td>{$functie->getStandaardPunten()}</td>
	<td title="{$functie->getEmailBericht()}">{if strlen($functie->getEmailBericht()) > 0}{icon get="email"}{/if}</td>
	<td>
		{if $functie->getIsKwalificatieBenodigd()}
			<div style="float: left;"><a href="{$instellingen->get('taken', 'url')}/kwalificeer/{$functie->getFunctieId()}" title="Kwalificatie toewijzen" class="knop post popup">{icon get="vcard_add"} Kwalificeer</a></div>
			<div class="kwali"><a title="Toon oudleden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon oudleden</a></div>
			<div class="kwali" style="display: none;"><a title="Toon leden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon leden</a></div>
		{/if}
		{foreach from=$functie->getGekwalificeerden() item=kwali}
			<div class="kwali"{if $kwali->getLid()->isOudlid()} style="display: none;"{/if}>
				<a href="{$instellingen->get('taken', 'url')}/dekwalificeer/{$functie->getFunctieId()}" title="Kwalificatie intrekken" class="knop post" postdata="voor_lid={$kwali->getLidId()}">{icon get="vcard_delete"}</a>
				&nbsp;{$kwali->getLid()->getNaamLink($instellingen->get('corvee', 'weergave_ledennamen_beheer'), $instellingen->get('corvee', 'weergave_link_ledennamen'))}
			</div>
		{/foreach}
	</td>
	<td class="col-del">
		<a href="{$instellingen->get('taken', 'url')}/verwijder/{$functie->getFunctieId()}" title="Functie definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
	</td>
</tr>