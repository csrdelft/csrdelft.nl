{*
	beheer_corvee_repetities.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u corveerepetities aanmaken, wijzigen en verwijderen{if isset($maaltijdrepetitie)} behorend bij de maaltijdrepetitie:<br />
<b>{$maaltijdrepetitie->getStandaardTitel()}</b>
<a href="/maaltijdenrepetities/beheer/{$maaltijdrepetitie->getMaaltijdRepetitieId()}" title="Wijzig gekoppelde maaltijd" class="knop modal">{icon get="calendar_edit"}</a>
</p><p>
{else}.
{/if}
Onderstaande tabel toont {if isset($maaltijdrepetitie)}<i>alleen</i> de corveerepetities voor deze maaltijdrepetitie{else}alle repetities in het systeem{/if}.
</p>
<h3>Repetities verwijderen</h3>
<p>
Voordat een corveerepetitie verwijderd kan worden moeten eerst alle bijbehorende corveetaken definitief zijn verwijderd.
Dit is dus inclusief maaltijdcorveetaken (die door een gekoppelde maaltijdrepetitie zijn aangemaakt).
Bij het verwijderen van een gekoppelde maaltijdrepetitie blijven de eventuele gekoppelde corveerepetities bestaan.
</p>
<p>
N.B. Als u kiest voor "Alles bijwerken" worden alle corveetaken die behoren tot de betreffende corveerepetitie bijgewerkt, ongeacht of ze tot een maaltijd behoren. Er worden ook extra taken aangemaakt tot aan het standaard aantal.
</p>
<div class="float-right">
	<a href="{Instellingen::get('taken', 'url')}/nieuw{if isset($maaltijdrepetitie)}/{$maaltijdrepetitie->getMaaltijdRepetitieId()}{/if}" title="Nieuwe repetitie" class="knop post modal">{icon get="add"} Nieuwe repetitie</a>
</div>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th style="width: 80px;">Wijzig</th>
			<th>Functie</th>
			<th>Dag</th>
			<th>Periode</th>
			<th>{icon get="tick" title="Voorkeurbaar"}</th>
			<th>Standaard<br />punten</th>
			<th>Aantal<br />corveeërs</th>
			<th title="Definitief verwijderen" style="text-align: center;">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$repetities item=repetitie}
	{include file='maalcie/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl' repetitie=$repetitie}
{/foreach}
	</tbody>
</table>