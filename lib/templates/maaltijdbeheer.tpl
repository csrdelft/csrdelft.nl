{if $maal.maaltijden|@count==0}
	<p>&#8226; Helaas, er is binnenkort geen maaltijd op Confide.</p>
{else}
	<table class="maaltijden">
		<tr>
			<th>&nbsp;</th>
			<th>Maaltijd begint om:</th>
			<th>Omschrijving</th>
			<th>Aantal aanmeldingen (Max)</th>
		</tr>
		{foreach from=$maal.maaltijden item=maaltijd}
			<tr>
				<td>
					<!--<a href="/maaltijden/beheer/bewerk/{$maaltijd.id}"><img src="{$csr_pics}forum/bewerken.png" /></a> -->
					<a href="/maaltijden/beheer/verwijder/{$maaltijd.id}" onclick="return confirm(\'Weet u zeker dat u deze maaltijd wilt verwijderen?\')"><img src="{$csr_pics}forum/verwijderen.png" /></a>
				</td>
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>{$maaltijd.tekst|escape:'html'}</td>
				<td>
					{if $maaltijd.aantal < $maaltijd.max}
						{$maaltijd.aantal} ({$maaltijd.max})
					{else}
						VOL ({$maaltijd.max})
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
{/if}



{* maaltijd bewerken of toevoegoen, standaard toevoegen *}
<span id="toevoegen"></span>
{include file='maaltijdedit.tpl'}


