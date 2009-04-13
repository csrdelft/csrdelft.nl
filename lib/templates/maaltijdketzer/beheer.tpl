<div style="float: right; margin: 0 0 10px 10px;">
	<a href="/actueel/maaltijden/voorkeuren.php" title="Instellingen">Instellingen</a>
	{if $loginlid->hasPermission('P_MAAL_MOD')}
		| <a href="/actueel/maaltijden/beheer/" title="Beheer">Beheer</a>
		| <a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
	{/if}
</div>
<h1>Maaltijdbeheer</h1>
{if $maal.maaltijden|@count==0}
	<p>&#8226; Helaas, er is binnenkort geen maaltijd op Confide.</p>
{else}
	<table class="maaltijden">
		<tr>
			<th>&nbsp;</th>
			<th>Maaltijd begint om:</th>
			<th>Omschrijving</th>
			<th>Abo</th>
			<th>Tafelpraeses</th>
			<th># (Max)</th>
		</tr>
		{foreach from=$maal.maaltijden item=maaltijd}
			<tr {if $maaltijd.datum<=$smarty.now}class="old"{/if}>
				<td>
					<a href="/actueel/maaltijden/beheer/bewerk/{$maaltijd.id}#maaltijdFormulier"><img src="{$csr_pics}forum/bewerken.png" /></a>
					<a href="/actueel/maaltijden/beheer/verwijder/{$maaltijd.id}" onclick="return confirm(\'Weet u zeker dat u deze maaltijd wilt verwijderen?\')"><img src="{$csr_pics}forum/verwijderen.png" /></a>
					<a href="/actueel/maaltijden/lijst/{$maaltijd.id}" class="knop">lijst</a>
					<a href="/actueel/maaltijden/lijst/{$maaltijd.id}/fiscaal" class="knop">&euro;</a>
				</td>
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>{$maaltijd.tekst|escape:'html'}</td>
				<td>{$maaltijd.abotekst}</td>
				<td>{$maaltijd.tp|csrnaam}</td>
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
	<br />
{/if}

{* maaltijd bewerken of toevoegoen, standaard toevoegen *}
{include file='maaltijdketzer/formulier.tpl'}
