{*
	beheer_taken.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{if $prullenbak}
	<p>Op deze pagina kunt u de corveetaken herstellen of definitief verwijderen. Onderstaande tabel toont alle corveetaken die in de prullenbak zitten.</p>
{else}
	<p>Op deze pagina kunt u de corveetaken aanmaken, wijzigen en verwijderen{if isset($maaltijd)}
		voor de
		{if $maaltijd->getIsVerwijderd()}
			<b>verwijderde</b>
		{/if}
			maaltijd:<br /><b>{$maaltijd->getTitel()} op {$maaltijd->getDatum()|date_format:"%A %e %B"} om {$maaltijd->getTijd()|date_format:"%H:%M"}</b>
		{if !$maaltijd->getIsVerwijderd()}
			<a href="/maaltijdenbeheer/beheer/{$maaltijd->getMaaltijdId()}" title="Wijzig gekoppelde maaltijd" class="knop get">{icon get="cup_edit"}</a>
		{/if}
		</p><p>Onderstaande tabel toont <i>alleen</i> de corveetaken voor deze maaltijd die <i>niet verwijderd</i> zijn.
	{else}.
		Onderstaande tabel toont alle corveetaken die niet verwijderd zijn.
	{/if}
	Taken in het verleden waarvoor wel iemand is ingedeeld maar geen punten zijn toegekend worden geel gemarkeerd.
	</p>
	<p>N.B. U kunt ingedeelde corveeërs eenvoudig ruilen door het icoontje voor de naam te verslepen.</p>
{/if}
<br />
{if !($prullenbak or (isset($maaltijd) and $maaltijd->getIsVerwijderd()))}
<div style="float: right;">
	<a class="knop" onclick="$(this).hide();taken_show_old();">{icon get="eye"} Toon verleden</a>
	<a href="{$globals.taken_module}/nieuw{if isset($maaltijd)}/{$maaltijd->getMaaltijdId()}{/if}" title="Nieuwe taak" class="knop post popup">{icon get="add"} Nieuwe taak</a>
</div>
{/if}
{if isset($repetities) and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
<form method="post" action="{$globals.taken_module}/nieuw{if isset($maaltijd)}/{$maaltijd->getMaaltijdId()}{/if}" class="Formulier popup">
	<label for="crid">{icon get="calendar_add"} Periodieke taken aanmaken:</label>
	<select name="crid" value="kies" origvalue="kies" class="regular" onchange="taken_submit_dropdown($(this).parent());">
		<option selected="selected">kies</option>
	{foreach from=$repetities item=repetitie}
		<option value="{$repetitie->getCorveeRepetitieId()}">{$repetitie->getCorveeFunctie()->getNaam()} op {$repetitie->getDagVanDeWeekText()}</option>
	{/foreach}
	</select>
</form>
{/if}
<br />
{if !$prullenbak and !isset($maaltijd)}
	{*<a href="/corveebeheer/indelen" title="Leden automatisch indelen voor taken" class="knop disabled">{icon get="date"} Automatisch indelen</a>*}
	<a href="/corveebeheer/herinneren" title="Verstuur herinneringen" class="knop get popup">{icon get="clock"} Herinneringen versturen</a>
{/if}
<table id="taken-tabel" class="taken-tabel">
{foreach name="tabel" from=$taken key=datum item=perdatum}
	{if $smarty.foreach.tabel.first}
	<thead>
	{include file='taken/corveetaak/beheer_taak_head.tpl' show="true" datum='first'}
	</thead>
	<tbody></tbody>
	{/if}
	{if !$prullenbak and !isset($maaltijd)}
	<thead>
		{include file='taken/corveetaak/beheer_taak_datum.tpl' perdatum=$perdatum datum=$datum}
	</thead>
	{/if}
	<tbody>
	{foreach from=$perdatum key="fid" item=perfunctie}
		{foreach from=$perfunctie item=taak}
			{include file='taken/corveetaak/beheer_taak_lijst.tpl' taak=$taak}
		{/foreach}
	{/foreach}
	</tbody>
{/foreach}
</table>