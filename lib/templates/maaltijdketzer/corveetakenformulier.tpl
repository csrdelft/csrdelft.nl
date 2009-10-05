<h2 id="corveetakenFormulier">Maaltijd Corvee {$maal.formulier.actie}</h2>
 
<form name="takenbewerk" action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	<input type="hidden" name="actie" value="takenbewerk" />
	<input type="hidden" id="filter" name="filter" value="{$maal.formulier.filter}" />
	
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<table>
		<tr>
			<td>
	<table>
		<tr>
			<td style="width: 120px">Beginmoment</td>
			<td>{$maal.formulier.datum|date_format:$datumFormaatInvoer}</td>
		</tr>		
		<tr>
			<td>Tafelpraeses</td>
			<td>
				{if $maal.formulier.tp!=''}{$maal.formulier.tp|csrnaam} {/if}
			</td>
		</tr>		
		<tr>
			<td>
					<tr>
						<td>
							Functie
						</td>
						<td>
							Persoon
						</td>
					</tr>
					<tr>
						<td>
							Koks({$maal.formulier.koks})
						</td>
						<td>
							{section name=koks loop=$maal.formulier.koks}			
								{assign var='it' value=$smarty.section.koks.iteration-1}
								{assign var='kok' value=$maal.formulier.taken.koks.$it}
								{if $it==0}
									{html_options name=kok[$it] options=$maal.formulier.kwalikoks selected=$kok} (Kwalikok)
								{else}
									{html_options name=kok[$it] options=$maal.formulier.kokleden selected=$kok}
								{/if}
								{if $kok!=''}
									{$kok|csrnaam}
								{/if}<br />
							{/section}
						</td>											
					</tr>
					<tr>
						<td>Afwassers ({$maal.formulier.afwassers})</td>
						<td>{section name=afwassers loop=$maal.formulier.afwassers}					
								{assign var='it' value=$smarty.section.afwassers.iteration-1}
								{assign var='afwasser' value=$maal.formulier.taken.afwassers.$it}
								{if $it==0}
									{html_options name=afwas[$it] options=$maal.formulier.afwasleden selected=$afwasser} (Kwali-afwasser)
								{else}
									{html_options name=afwas[$it] options=$maal.formulier.afwasleden selected=$afwasser}
								{/if}
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
					<tr>
						<td>&nbsp;</td>
						<Td><input type="submit" name="opslaan" value="opslaan" /> <input type="button" value="Herlaad zonder filter" onClick="document.getElementById('filter').value=0;document.forms['takenbewerk'].submit();" /></td>
					</tr>
			</td>
		</tr>
	</table>
			</td>
			<td>
	{if $maal.formulier.datum<=$smarty.now}	
	<table>
		<tr>
			<td>
				Corveepunten
			</td>
		</tr>
		{foreach from=$maal.formulier.taken.toegekend key=lid item=selected}
		<tr>
			<td>
				{$lid|csrnaam}
			</td>
			<td>
				{$selected}
			</td>
			<td>
				{if $selected=="onbekend"}	
					{html_options name=punten[$lid] options=$maal.formulier.pt_opties selected="ja"}
				{else}
					{html_options name=punten[$lid] options=$maal.formulier.pt_opties selected=$selected}
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	{/if}
			</td>
		</tr>
	</table>
</form>
