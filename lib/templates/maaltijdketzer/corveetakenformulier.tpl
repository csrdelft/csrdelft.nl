<h2 id="corveetakenFormulier">{if $maal.formulier.type == "normaal"}Maaltijdcorvee{else}Huishoudelijke {/if}taken bewerken</h2>

<form name="takenbewerk" action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="actie" value="takenbewerk" />
	<input type="hidden" name="type" value="{$maal.formulier.type}" />
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	<input type="hidden" id="filter" name="filter" value="{$maal.formulier.filter}" />

	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<table class="corveetakenselects">
		<tr>
			<td style="width: 120px">Beginmoment</td>
			<td>{$maal.formulier.datum|date_format:$datumFormaatInvoer}</td>
		</tr>
		{if $maal.formulier.type == "normaal"}
			<tr>
				<td>Tafelpraeses</td>
				<td>
					{if $maal.formulier.tp!=''}{$maal.formulier.tp|csrnaam} {/if}
				</td>
			</tr>
		{/if}
		{if $maal.formulier.type == "normaal"}
			<tr>
				<td>
					Koks ({$maal.formulier.koks + $maal.formulier.kwalikoks})
				</td>
				<td>
					{section name=kwalikoks loop=$maal.formulier.kwalikoks}
						{assign var='it' value=$smarty.section.kwalikoks.iteration-1}
						{assign var='kwalikok' value=$maal.formulier.taken.kwalikoks.$it}
						{html_options name=kwalikok[$it] options=$maal.formulier.kwalikokleden selected=$kwalikok} 
						{if $kwalikok!=''}{$kwalikok|csrnaam}{/if} (Kwalikok)<br />
					{/section}
					{section name=koks loop=$maal.formulier.koks}
						{assign var='it' value=$smarty.section.koks.iteration-1}
						{assign var='kok' value=$maal.formulier.taken.koks.$it}
						{html_options name=kok[$it] options=$maal.formulier.kokleden selected=$kok} 
						{if $kok!=''}{$kok|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
			<tr>
				<td>Afwassers ({$maal.formulier.afwassers+$maal.formulier.kwaliafwassers})</td>
				<td>
					{if $maal.formulier.kwaliafwassers>0}
						{assign var='kwaliafwasser' value=$maal.formulier.taken.kwaliafwassers.0}
						{html_options name=kwaliafwas[0] options=$maal.formulier.kwaliafwasleden selected=$kwaliafwasser} 
						{if $kwaliafwasser!=''}{$kwaliafwasser|csrnaam}{/if} (Kwali-afwasser)<br />
					{/if}
					{section name=afwassers loop=$maal.formulier.afwassers}
						{assign var='it' value=$smarty.section.afwassers.iteration-1}
						{assign var='afwasser' value=$maal.formulier.taken.afwassers.$it}
						{html_options name=afwas[$it] options=$maal.formulier.afwasleden selected=$afwasser} 
						{if $afwasser!=''}{$afwasser|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
			<tr>
				<td>Theedoeken ({$maal.formulier.theedoeken})</td>
				<td>{section name=theedoeken loop=$maal.formulier.theedoeken}					
						{assign var='it' value=$smarty.section.theedoeken.iteration-1}
						{assign var='theedoeker' value=$maal.formulier.taken.theedoeken.$it}
						{html_options name=theedoek[$it] options=$maal.formulier.theedoekleden selected=$theedoeker}
						{if $theedoeker!=''}{$theedoeker|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
		{else} {* Huishoudelijke taak *}
			<tr>
				<td style="width: 160px">
					Frituurschoonmakers ({$maal.formulier.schoonmaken_frituur})
				</td>
				<td>
					{section name=schoonmaken_frituur loop=$maal.formulier.schoonmaken_frituur}			
						{assign var='it' value=$smarty.section.schoonmaken_frituur.iteration-1}
						{assign var='frituur' value=$maal.formulier.taken.schoonmaken_frituur.$it}
						{html_options name=frituur[$it] options=$maal.formulier.frituurleden selected=$frituur}
						{if $frituur!=''}{$frituur|csrnaam}{/if}<br />
					{/section}
				</td>											
			</tr>
			<tr>
				<td>Afzuigkapschoonmakers ({$maal.formulier.schoonmaken_afzuigkap})</td>
				<td>{section name=schoonmaken_afzuigkap loop=$maal.formulier.schoonmaken_afzuigkap}					
						{assign var='it' value=$smarty.section.schoonmaken_afzuigkap.iteration-1}
						{assign var='afzuigkap' value=$maal.formulier.taken.schoonmaken_afzuigkap.$it}
						{html_options name=afzuigkap[$it] options=$maal.formulier.afzuigkapleden selected=$afzuigkap}
						{if $afzuigkap!=''}{$afzuigkap|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
			<tr>
				<td>Keukenschoonmakers ({$maal.formulier.schoonmaken_keuken})</td>
				<td>{section name=schoonmaken_keuken loop=$maal.formulier.schoonmaken_keuken}					
						{assign var='it' value=$smarty.section.schoonmaken_keuken.iteration-1}
						{assign var='keuken' value=$maal.formulier.taken.schoonmaken_keuken.$it}
						{html_options name=keuken[$it] options=$maal.formulier.keukenleden selected=$keuken}
						{if $keuken!=''}{$keuken|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
			<tr>
				<td>Klussers (Licht) ({$maal.formulier.klussen_licht})</td>
				<td>{section name=klussen_licht loop=$maal.formulier.klussen_licht}					
						{assign var='it' value=$smarty.section.klussen_licht.iteration-1}
						{assign var='lichteklus' value=$maal.formulier.taken.klussen_licht.$it}
						{html_options name=lichteklus[$it] options=$maal.formulier.lichteklusleden selected=$lichteklus}
						{if $lichteklus!=''}{$lichteklus|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
			<tr>
				<td>Klussers (Zwaar) ({$maal.formulier.klussen_zwaar})</td>
				<td>{section name=klussen_zwaar loop=$maal.formulier.klussen_zwaar}					
						{assign var='it' value=$smarty.section.klussen_zwaar.iteration-1}
						{assign var='zwareklus' value=$maal.formulier.taken.klussen_zwaar.$it}
						{html_options name=zwareklus[$it] options=$maal.formulier.zwareklusleden selected=$zwareklus}
						{if $zwareklus!=''}{$zwareklus|csrnaam}{/if}<br />
					{/section}
				</td>
			</tr>
		{/if}
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="opslaan" value="Opslaan" /> <input type="button" value="Opslaan & herlaad {if $maal.formulier.filter==1}zonder{else}met{/if} voorkeurenfilter" onClick="document.getElementById('filter').value={if $maal.formulier.filter==1}0{else}1{/if};document.forms['takenbewerk'].submit();" /> <input type="button" value="Herlaad {if $maal.formulier.filter==1}zonder{else}met{/if} voorkeurenfilter" onClick="document.getElementById('filter').value=0;location.href = '/actueel/maaltijden/corveebeheer/takenbewerk/{$maal.formulier.id}/{if $maal.formulier.filter==1}0{else}1{/if}#corveetakenFormulier';" /></td>
		</tr>
	</table>
</form>
