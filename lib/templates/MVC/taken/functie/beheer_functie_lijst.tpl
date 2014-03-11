{*
	beheer_functie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveefunctie-row-{$functie->functie_id}">
	<td>
		<a href="{$instellingen->get('taken', 'url')}/bewerken/{$functie->functie_id}" title="Functie wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</td>
	<td>{$functie->afkorting}</td>
	<td>{$functie->naam}</td>
	<td>{$functie->standaard_punten}</td>
	<td title="{$functie->email_bericht}">{if strlen($functie->email_bericht) > 0}{icon get="email"}{/if}</td>
	<td>
		{if $functie->kwalificatie_benodigd}
			<div style="float: left;"><a href="{$instellingen->get('taken', 'url')}/kwalificeer/{$functie->functie_id}" title="Kwalificatie toewijzen" class="knop post popup">{icon get="vcard_add"} Kwalificeer</a></div>
			<div class="kwali"><a title="Toon oudleden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon oudleden</a></div>
			<div class="kwali" style="display: none;"><a title="Toon leden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon leden</a></div>
		{/if}
		{foreach from=$functie->gekwalificeerden item=kwali}
			<div class="kwali"{if $kwali->getLid()->isOudlid()} style="display: none;"{/if}>
				<a href="{$instellingen->get('taken', 'url')}/dekwalificeer/{$functie->functie_id}" title="Kwalificatie intrekken" class="knop post" postdata="voor_lid={$kwali->getLidId()}">{icon get="vcard_delete"}</a>
				&nbsp;{$kwali->getLid()->getNaamLink($instellingen->get('corvee', 'weergave_ledennamen_beheer'), $instellingen->get('corvee', 'weergave_link_ledennamen'))}
			</div>
		{/foreach}
	</td>
	<td class="col-del">
		<a href="{$instellingen->get('taken', 'url')}/verwijderen/{$functie->functie_id}" title="Functie definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
	</td>
</tr>