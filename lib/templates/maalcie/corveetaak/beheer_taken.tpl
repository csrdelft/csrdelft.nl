{* beheer_taken.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{if $prullenbak}
	<p>Op deze pagina kunt u de corveetaken herstellen of definitief verwijderen. Onderstaande tabel toont alle corveetaken die in de prullenbak zitten.</p>
{else}
	<p>Op deze pagina kunt u de corveetaken aanmaken, wijzigen en verwijderen
		{if isset($maaltijd)} voor de
			{if $maaltijd->getArchief() !== null}
				<span class="dikgedrukt">gearchiveerde</span>
			{elseif $maaltijd->getIsVerwijderd()}
				<span class="dikgedrukt">verwijderde</span>
			{/if}
			maaltijd:<br />
			{if $maaltijd->getArchief() !== null}
				{icon get="compress" title="Maaltijd is gearchiveerd"}
			{elseif $maaltijd->getIsVerwijderd()}
				{icon get="bin" title="Maaltijd is verwijderd"}
			{else}
				<a href="/maaltijdenbeheer/beheer/{$maaltijd->getMaaltijdId()}" title="Wijzig gekoppelde maaltijd" class="btn rounded modal">{icon get="cup_edit"}</a>
			{/if}
			<span class="dikgedrukt">{$maaltijd->getTitel()} op {$maaltijd->getDatum()|date_format:"%A %e %B"} om {$maaltijd->getTijd()|date_format:"%H:%M"}</span>
		</p>
		{if $maaltijd->getIsVerwijderd()}
			<p>Onderstaande tabel toont de corveetaken voor deze maaltijd, ook die verwijderd zijn.
			{else}
			<p>Onderstaande tabel toont <span class="cursief">alleen</span> de corveetaken voor deze maaltijd die <span class="cursief">niet verwijderd</span> zijn.
			{/if}
		{else}.
			Onderstaande tabel toont alle corveetaken die niet verwijderd zijn.
		{/if}
		Taken in het verleden waarvoor wel iemand is ingedeeld maar geen punten zijn toegekend worden geel gemarkeerd.
	</p>
	<p>N.B. U kunt ingedeelde corveeërs eenvoudig ruilen door het icoontje voor de naam te verslepen.</p>
{/if}
<br />

<div class="float-right">
	{*if !$prullenbak and !isset($maaltijd)}
		<a href="/corveebeheer/indelen" title="Leden automatisch indelen voor taken" class="btn">{icon get="date"} Automatisch indelen</a>
		<a href="/corveebeheer/herinneren" title="Verstuur herinneringen" class="btn">{icon get="clock"} Herinneringen versturen</a>
	{/if*}
	{if !($prullenbak or (isset($maaltijd) and $maaltijd->getIsVerwijderd()))}
		<a class="btn" onclick="$(this).hide(); taken_show_old();">{icon get="eye"} Toon verleden</a>
		<a href="{$smarty.const.maalcieUrl}/nieuw{if isset($maaltijd)}/{$maaltijd->getMaaltijdId()}{/if}" title="Nieuwe taak" class="btn post popup">{icon get="add"} Nieuwe taak</a>
	{/if}
</div>

{if isset($repetities) and (!isset($maaltijd) or !$maaltijd->getIsVerwijderd())}
	<form action="{$smarty.const.maalcieUrl}/nieuw{if isset($maaltijd)}/{$maaltijd->getMaaltijdId()}{/if}" method="post" class="Formulier modal SubmitReset">
		<label for="crid" style="width: auto;">{icon get="calendar_add"} Periodieke taken aanmaken:</label>&nbsp;
		<select name="crid" value="kies" origvalue="kies" class="FormElement SubmitChange">
			<option selected="selected">kies</option>
			{foreach from=$repetities item=repetitie}
				<option value="{$repetitie->getCorveeRepetitieId()}">{$repetitie->getCorveeFunctie()->naam} op {$repetitie->getDagVanDeWeekText()}</option>
			{/foreach}
		</select>
	</form>
{/if}
<br />

<table id="maalcie-tabel" class="maalcie-tabel">
	{foreach name="tabel" from=$taken key=datum item=perdatum}
		{if $smarty.foreach.tabel.first}
			<thead>
				{include file='maalcie/corveetaak/beheer_taak_head.tpl' show="true" datum='first'}
			</thead>
			<tbody></tbody>
		{/if}
		{if !$prullenbak and !isset($maaltijd)}
			<thead>
				{include file='maalcie/corveetaak/beheer_taak_datum.tpl' perdatum=$perdatum datum=$datum}
			</thead>
			<tbody>
			{/if}
			{foreach from=$perdatum key="fid" item=perfunctie}
				{foreach from=$perfunctie item=taak}
					{include file='maalcie/corveetaak/beheer_taak_lijst.tpl' taak=$taak}
				{/foreach}
			{/foreach}
			{if !$prullenbak and !isset($maaltijd)}
			</tbody>
		{/if}
	{/foreach}
</table>