{assign var='actief' value='corveepunten'}
{include file='maaltijdketzer/menu.tpl'}

<h1>Corveepunten</h1>

<table class="maaltijden">
	{section name=leden loop=$leden}
		{assign var='it' value=$smarty.section.leden.iteration-1}
		{if $it%30 == 0}
			<tr>
				<th>&nbsp;</th>
				<th><a href="/actueel/maaltijden/corveepunten/sorteer/achternaam/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Naam</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveepunten/sorteer/kok/{if $sorteer_richting=='asc'}desc{else}asc{/if}">K</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveepunten/sorteer/afwas/{if $sorteer_richting=='asc'}desc{else}asc{/if}">A</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveepunten/sorteer/theedoek/{if $sorteer_richting=='asc'}desc{else}asc{/if}">T</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveepunten/sorteer/schoonmaken_frituur/{if $sorteer_richting=='asc'}desc{else}asc{/if}">sF</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveepunten/sorteer/schoonmaken_afzuigkap/{if $sorteer_richting=='asc'}desc{else}asc{/if}">sA</a></th>
				<th style="width: 30px"><a href="/actueel/maaltijden/corveepunten/sorteer/schoonmaken_keuken/{if $sorteer_richting=='asc'}desc{else}asc{/if}">sK</a></th>
				<th style="width: 60px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_kwalikok/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Kwalikok</a></th>
				<th style="width: 50px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_punten_bonus/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Bonus</a></th>
				<th style="width: 75px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_vrijstelling/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Vrijstelling</a></th>
				<th style="width: 60px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_punten/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Punten</a></th>
				<th style="width: 60px;"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_tekort/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Tekort</a></th>
				<th colspan="2"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_prognose/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Prognose</a></th>
			</tr>
		{/if}
		{assign var='lid' value=$leden.$it.uid}
		{if $lid!=''}
			{if $loginlid->hasPermission('P_MAAL_MOD')}
				<form id="{$lid}" action="/actueel/maaltijden/corveepunten/#lid_{$lid}" method="post">
				<input type="hidden" name="uid" value="{$lid}" />
				<input type="hidden" name="sorteer" value="{$sorteer}" />
				<input type="hidden" name="sorteer_richting" value="{$sorteer_richting}" />
				<input type="hidden" name="actie" value="bewerk" />

				<tr style="background-color: {cycle values="#e9e9e9, #fff"};{if $bewerkt_lid==$lid}background-color: #bfb{else}{/if}">
					<td><a name="lid_{$lid}"></a></td>
					<td>{$lid|csrnaam}</td>
					<td>{if $leden.$it.kok}{$leden.$it.kok}{/if}</td>
					<td>{if $leden.$it.afwas}{$leden.$it.afwas}{/if}</td>
					<td>{if $leden.$it.theedoek}{$leden.$it.theedoek}{/if}</td>
					<td>{if $leden.$it.schoonmaken_frituur}{$leden.$it.schoonmaken_frituur}{/if}</td>
					<td>{if $leden.$it.schoonmaken_afzuigkap}{$leden.$it.schoonmaken_afzuigkap}{/if}</td>
					<td>{if $leden.$it.schoonmaken_keuken}{$leden.$it.schoonmaken_keuken}{/if}</td>
					<td><input type="checkbox" name="corvee_kwalikok" value="1" {if $leden.$it.corvee_kwalikok}checked="checked"{/if} /></td>
					<td><input type="text" name="corvee_punten_bonus" value="{$leden.$it.corvee_punten_bonus}" style="width: 30px;" /></td>
					<td><input type="text" name="corvee_vrijstelling" value="{$leden.$it.corvee_vrijstelling}" style="width: 30px;" />%</td>
					<td style="background-color: #{$leden.$it.corvee_punten_rgb}"><input type="text" name="corvee_punten" value="{$leden.$it.corvee_punten}" style="width: 30px;" /></td>
					<td style="background-color: #{$leden.$it.corvee_tekort_rgb}">{$leden.$it.corvee_tekort}</td>
					<td style="width: 60px; background-color: #{$leden.$it.corvee_prognose_rgb}">{$leden.$it.corvee_prognose}</td>
					<td style="width: 20px;"><input type="submit" name="submit" value="OK" /></td>
				</tr>
				
				</form>
			{else}
				<tr style="background-color: {cycle values="#e9e9e9, #fff"}">
					<td></td>
					<td>{$lid|csrnaam}</td>
					<td>{if $leden.$it.kok}{$leden.$it.kok}{/if}</td>
					<td>{if $leden.$it.afwas}{$leden.$it.afwas}{/if}</td>
					<td>{if $leden.$it.theedoek}{$leden.$it.theedoek}{/if}</td>
					<td>{if $leden.$it.schoonmaken_frituur}{$leden.$it.schoonmaken_frituur}{/if}</td>
					<td>{if $leden.$it.schoonmaken_afzuigkap}{$leden.$it.schoonmaken_afzuigkap}{/if}</td>
					<td>{if $leden.$it.schoonmaken_keuken}{$leden.$it.schoonmaken_keuken}{/if}</td>
					<td>{$leden.$it.corvee_kwalikok}</td>
					<td>{$leden.$it.corvee_punten_bonus}</td>
					<td>{$leden.$it.corvee_vrijstelling}%</td>
					<td style="background-color: #{$leden.$it.corvee_punten_rgb}">{$leden.$it.corvee_punten}</td>
					<td style="background-color: #{$leden.$it.corvee_tekort_rgb}">{$leden.$it.corvee_tekort}</td>
					<td style="width: 60px; background-color: #{$leden.$it.corvee_prognose_rgb}">{$leden.$it.corvee_prognose}</td>
					<td></td>
				</tr>
			{/if}
		{else}FOUT{/if}
	{/section}
</table>
<br />
