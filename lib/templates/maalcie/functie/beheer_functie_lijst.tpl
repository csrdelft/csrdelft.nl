{*
	beheer_functie_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="corveefunctie-row-{$functie->functie_id}">
	<td>
		<a href="{$smarty.const.maalcieUrl}/bewerken/{$functie->functie_id}" title="Functie wijzigen" class="btn post popup">{icon get="pencil"}</a>
	</td>
	<td>{$functie->afkorting}</td>
	<td>{$functie->naam}</td>
	<td>{$functie->standaard_punten}</td>
	<td title="{$functie->email_bericht}">{if strlen($functie->email_bericht) > 0}{icon get="email"}{/if}</td>
	<td>
		{if $functie->kwalificatie_benodigd}
			<div class="float-left"><a href="{$smarty.const.maalcieUrl}/kwalificeer/{$functie->functie_id}" title="Kwalificatie toewijzen" class="btn post popup">{icon get="vcard_add"} Kwalificeer</a></div>
		{/if}
		{if $functie->hasKwalificaties()}
			<div class="kwali"><a title="Toon oudleden" class="btn" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon oudleden</a></div>
			<div class="kwali verborgen"><a title="Toon leden" class="btn" onclick="$('div.kwali').toggle();">{icon get="eye"} Toon leden</a></div>
		{/if}
		{foreach from=$functie->getKwalificaties() item=kwali}
			<div class="kwali{if CsrDelft\model\ProfielModel::get($kwali->uid)->isOudlid()} verborgen{/if}">
				<a href="{$smarty.const.maalcieUrl}/dekwalificeer/{$functie->functie_id}/{$kwali->uid}" title="Kwalificatie intrekken" class="btn post">{icon get="vcard_delete"}</a>
				&nbsp;{CsrDelft\model\ProfielModel::get($kwali->uid)->getNaam(instelling('corvee', 'weergave_ledennamen_beheer'))}
				<span class="lichtgrijs"> (sinds {$kwali->wanneer_toegewezen})</span>
			</div>
		{/foreach}
	</td>
	<td title="Mag maaltijden sluiten">{if $functie->maaltijden_sluiten}{icon get="lock_add"}{/if}</td>
	<td class="col-del">
		<a href="{$smarty.const.maalcieUrl}/verwijderen/{$functie->functie_id}" title="Functie definitief verwijderen" class="btn post confirm">{icon get="cross"}</a>
	</td>
</tr>
