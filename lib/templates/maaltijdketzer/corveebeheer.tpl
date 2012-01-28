{assign var='actief' value='corveebeheer'}
{include file='maaltijdketzer/menu.tpl'}

<h1>Corveebeheer</h1>
{if $maal.zelf.error!=''}<span class="waarschuwing">N.B.: {$maal.zelf.error|escape:'htmlall'}</span><br /><br />{/if}
{if $maal.maaltijden|@count==0}
	<p>&#8226; Helaas, er is binnenkort geen maaltijd op Confide.</p>
{else}
	<table class="maaltijden" id="corveebeheer" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th>Maaltijd begint om:</th>
			<th>Omschrijving</th>
			<th>K</th>
			<th>A</th>
			<th>T</th>
			<th>sF</th>
			<th>sA</th>
			<th>sK</th>
			<th>kL</th>
			<th>kZ</th>
			<th>Punten</th>
			<th># (Max)</th>
			<th>&nbsp;</th>
		</tr>
		{foreach from=$maal.maaltijden item=maaltijd name=corveebit}
		<tr class="{if $maaltijd.datum<=$smarty.now}old{/if}{if $smarty.foreach.corveebit.index == 0} first{/if}" style="background-color: {cycle values="#e9e9e9, #fff"};{if $maal.formulier.id==$maaltijd.id}background-color: #bfb{/if}">
			<td style="width:100px">
				<a href="/actueel/maaltijden/corveebeheer/bewerk/{$maaltijd.id}#corveemaaltijdFormulier">{icon get="bewerken" title="Bewerk Maaltijd"}</a>					
				<a href="/actueel/maaltijden/corveebeheer/takenbewerk/{$maaltijd.id}#corveetakenFormulier">{icon get="taken_bewerken" title="Bewerk Taken"}</a>
				<a href="/actueel/maaltijden/corveebeheer/puntenbewerk/{$maaltijd.id}#corveepuntenFormulier">
					{if !$maaltijd.is_toegekend}{icon get="punten_bewerken" title="Punten Toekennen"}{else}{icon get="punten_bewerken_toegekend" title="Punten Toegekend!"}{/if}
				</a>
				{if $maaltijd.type == 'corvee'}
					<a href="/actueel/maaltijden/corveebeheer/verwijder/{$maaltijd.id}" onclick="return confirm('Weet u zeker dat u deze corveemaaltijd wilt verwijderen?')">{icon get="verwijderen"}</a>
				{/if}
			</td>
			<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
			<td>{$maaltijd.tekst|truncate:20|escape:'html'}</td>
			{if $maaltijd.type == "normaal"}
				<td {if ($maaltijd.kwalikoks+$maaltijd.koks) - ($maaltijd.kwalikoks_aangemeld+$maaltijd.koks_aangemeld) > 0}style="color: red;"{/if}>
					{$maaltijd.kwalikoks_aangemeld+$maaltijd.koks_aangemeld}/{$maaltijd.kwalikoks+$maaltijd.koks}
				</td>				
				<td {if ($maaltijd.kwaliafwassers+$maaltijd.afwassers) - ($maaltijd.kwaliafwassers_aangemeld+$maaltijd.afwassers_aangemeld) > 0}style="color: red;"{/if}>
					{$maaltijd.kwaliafwassers_aangemeld+$maaltijd.afwassers_aangemeld}/{$maaltijd.kwaliafwassers+$maaltijd.afwassers}
				</td>	
				<td {if $maaltijd.theedoeken - $maaltijd.theedoeken_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.theedoeken_aangemeld}/{$maaltijd.theedoeken}
				</td>	
				<td />
				<td />
				<td />
				<td />
				<td />
				<td {if $maaltijd.is_toegekend}style="color: #0D0;"{/if}>({$maaltijd.punten_kok}/{$maaltijd.punten_afwas}/{$maaltijd.punten_theedoek})</td>
				<td>
					{$maaltijd.aantal} ({$maaltijd.max})
				</td>
			{else} {* Huishoudelijke taak *}	
				<td />
				<td />
				<td />
				<td {if $maaltijd.schoonmaken_frituur - $maaltijd.frituur_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.frituur_aangemeld}/{$maaltijd.schoonmaken_frituur}
				</td>
				<td {if $maaltijd.schoonmaken_afzuigkap - $maaltijd.afzuigkap_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.afzuigkap_aangemeld}/{$maaltijd.schoonmaken_afzuigkap}
				</td>
				<td {if $maaltijd.schoonmaken_keuken - $maaltijd.keuken_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.keuken_aangemeld}/{$maaltijd.schoonmaken_keuken}
				</td>
				<td {if $maaltijd.klussen_licht - $maaltijd.lichteklus_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.lichteklus_aangemeld}/{$maaltijd.klussen_licht}
				</td>
				<td {if $maaltijd.klussen_zwaar - $maaltijd.zwareklus_aangemeld > 0}style="color: red;"{/if}>
					{$maaltijd.zwareklus_aangemeld}/{$maaltijd.klussen_zwaar}
				</td>
				<td {if $maaltijd.is_toegekend}style="color: #0D0;"{/if}>
					({$maaltijd.punten_schoonmaken_frituur}/{$maaltijd.punten_schoonmaken_afzuigkap}/{$maaltijd.punten_schoonmaken_keuken}/{$maaltijd.punten_klussen_licht}/{$maaltijd.punten_klussen_zwaar})
				</td>
				<td />
			{/if}
			<td>
				{if $maaltijd.corvee_gemaild == "1"}
					<img src="{icon get="gemaildoranje" notag=true}" alt="Een keer gemaild" title="Eén keer gemaild" />
				{elseif  $maaltijd.corvee_gemaild == "2"}
					<img src="{icon get="gemaild" notag=true}" alt="Twee keer gemaild" title="Twee keer gemaild" />
				{else}
					<img src="{icon get="niet_gemaild" notag=true}" alt="Niet gemaild" title="Niet gemaild" />
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	<br />
{/if}
{if $maal.formulier.actie == "bewerk" || $maal.formulier.actie == "toevoegen"}
	{* maaltijd bewerken *}
	{include file='maaltijdketzer/corveeformulier.tpl'}
{elseif $maal.formulier.actie == "puntenbewerk"}
	{* corvee-punten toekennnen *}
	{include file='maaltijdketzer/corveepuntenformulier.tpl'}
{elseif $maal.formulier.actie == "takenbewerk"}
	{* corvee-aanmeldingen bewerken *}
	{include file='maaltijdketzer/corveetakenformulier.tpl'}
{/if}
