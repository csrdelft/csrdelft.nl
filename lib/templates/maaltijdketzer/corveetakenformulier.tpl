<h2 id="corveetakenFormulier">Maaltijd Corvee {$maal.formulier.actie}</h2>

<form action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	<input type="hidden" name="actie" value="takenbewerk" />
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
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
			<td>Koks({$maal.formulier.koks})</td>
			<td>{section name=koks loop=$maal.formulier.koks}					
					{assign var='it' value=$smarty.section.koks.iteration-1}
					{assign var='kok' value=$maal.formulier.taken.koks.$it}
					{html_options name=kok[$it] options=$maal.formulier.taakleden selected=$kok}
					{if $kok!=''}{$kok|csrnaam}{/if}<br />
				{/section}</td>
		</tr>
		<tr>
			<td>Afwassers ({$maal.formulier.afwassers})</td>
			<td>{section name=afwassers loop=$maal.formulier.afwassers}					
					{assign var='it' value=$smarty.section.afwassers.iteration-1}
					{assign var='afwasser' value=$maal.formulier.taken.afwassers.$it}
					{html_options name=afwas[$it] options=$maal.formulier.taakleden selected=$afwasser}
					{if $afwasser!=''}{$afwasser|csrnaam}{/if}<br />
				{/section}</td>
		</tr>
		<tr>
			<td>Theedoeken ({$maal.formulier.theedoeken})</td>
			<td>{section name=theedoeken loop=$maal.formulier.theedoeken}					
					{assign var='it' value=$smarty.section.theedoeken.iteration-1}
					{assign var='theedoeker' value=$maal.formulier.taken.theedoeken.$it}
					{html_options name=theedoek[$it] options=$maal.formulier.taakleden selected=$theedoeker}
					{if $theedoeker!=''}{$theedoeker|csrnaam}{/if}<br />
				{/section}</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<Td><input type="submit" name="submit" value="opslaan" /></td>
		</tr>
	</table>
</form>
