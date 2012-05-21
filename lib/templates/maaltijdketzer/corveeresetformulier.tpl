{*
 * inhoud voor corveeresetformulier
 *}

{if $actie=='controleren_ongeldigedatum'}
	<label for="submit">&nbsp;</label>{$melding}
	<label for="submit">&nbsp;</label><input type="button" value="Ok, ik pas nog wat aan" onclick="restoreCorveeResetter()" />

{elseif $actie=='controleren'}
	<label for="button">&nbsp;</label><input type="button" value="Periodeeinde aanpassen" onclick="restoreCorveeResetter()" />
	<br/><br/>
	<label for="h2">&nbsp;</label><h2>Taken met niet toegekende punten </h2>
	<p>
		t/m {$datum}<br/><br/>
	</p>
	<label for="table">Te verwerken taken&nbsp;</label>
	<table id="resetcontrolletabel">
		<th>Datum</th><th>Corveegelegenheid</th><th>Corveeër</th>
		{foreach from=$alletaken item=taak}
			<tr>
				<td>
					{if $taak.datum===null}
						 -
					{else}
						{$taak.datum|date_format:"%a %d %b %Y"}
					{/if}
				</td>
				<td>
					<a href="/actueel/maaltijden/corveebeheer/puntenbewerk/{$taak.maalid}#corveepuntenFormulier">
						{if $taak.tekst===null}
							<span class="melding">Geen maaltijd gevonden..</span>
						{else}
							{$taak.tekst|escape:'html'}
						{/if}
					</a>
				</td>
				<td>
				{foreach from=$taak.corveeers item=corveeer}
					{$corveeer.uid|csrnaam:'civitas'}<br/>
				{/foreach}
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="3"><span class="melding">t/m {$datum} geen taken met niet-toegekende punten.</span></td>
			</tr>
		{/foreach}
	</table>
	<br/>
	<label for="submit">&nbsp;</label>
	<input type="button" value="Periodeeinde aanpassen" onclick="restoreCorveeResetter()" />&nbsp;
	<input type="button" name="submit2" id="submit" value="Afgelopen corveeperiode resetten" 
		onclick="{literal}if(confirm('De reset verlaagt corveepunten. Wilt u de reset uitvoeren?')){corveeResetter('resetcorveejaar');} return false;{/literal}" />

{elseif $actie=='resetcorveejaar' OR $actie=='resetmislukt'}
	<label for="submit">&nbsp;</label><div class="notred">{$melding}</div>
{/if}
