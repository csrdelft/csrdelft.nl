{*
	beheer_functie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveefunctie-row-{$functie->functie_id}">
	<td>
		<a href="{Instellingen::get('taken', 'url')}/bewerken/{$functie->functie_id}" title="Functie wijzigen" class="knop rounded post modal">{icon get="pencil"}</a>
	</td>
	<td>{$functie->afkorting}</td>
	<td>{$functie->naam}</td>
	<td>{$functie->standaard_punten}</td>
	<td title="{$functie->email_bericht}">{if strlen($functie->email_bericht) > 0}{icon get="email"}{/if}</td>
	<td>
		{if $functie->kwalificatie_benodigd}
			<div class="float-left"><a href="{Instellingen::get('taken', 'url')}/kwalificeer/{$functie->functie_id}" title="Kwalificatie toewijzen" class="knop post modal">{icon get="vcard_add"} Kwalificeer</a></div>
		{/if}
		{if $functie->hasKwalificaties()}
			<div class="kwali"><a title="Toon oudleden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon oudleden</a></div>
			<div class="kwali verborgen"><a title="Toon leden" class="knop" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon leden</a></div>
		{/if}
		{foreach from=$functie->getKwalificaties() item=kwali}
			<div class="kwali{if LidCache::getLid($kwali->uid)->isOudlid()} verborgen{/if}">
				<a href="{Instellingen::get('taken', 'url')}/dekwalificeer/{$functie->functie_id}/{$kwali->uid}" title="Kwalificatie intrekken" class="knop post">{icon get="vcard_delete"}</a>
				&nbsp;{LidCache::getLid($kwali->uid)->getNaamLink(Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}
				<span class="lichtgrijs"> (sinds {$kwali->wanneer_toegewezen})</span>
			</div>
		{/foreach}
	</td>
	<td title="Mag maaltijden sluiten">{if $functie->maaltijden_sluiten}{icon get="cup_key"}{/if}</td>
	<td class="col-del">
		<a href="{Instellingen::get('taken', 'url')}/verwijderen/{$functie->functie_id}" title="Functie definitief verwijderen" class="knop rounded post confirm">{icon get="cross"}</a>
	</td>
</tr>