{* beheer_taken.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{if $prullenbak}
	<p>Op deze pagina kunt u de corveetaken herstellen of definitief verwijderen. Onderstaande tabel toont alle corveetaken die in de prullenbak zitten.</p>
	<br />
{else}
	<p>Op deze pagina kunt u de corveetaken aanmaken, wijzigen en verwijderen
		{if isset($maaltijd)} voor de
			{if $maaltijd->archief !== null}
				<span class="dikgedrukt">gearchiveerde</span>
			{elseif $maaltijd->verwijderd}
				<span class="dikgedrukt">verwijderde</span>
			{/if}
			maaltijd:<br />
			{if $maaltijd->archief !== null}
				{icon get="compress" title="Maaltijd is gearchiveerd"}
			{elseif $maaltijd->verwijderd}
				{icon get="bin" title="Maaltijd is verwijderd"}
			{else}
				<a href="/maaltijdenbeheer/beheer/{$maaltijd->maaltijd_id}" title="Wijzig gekoppelde maaltijd" class="btn popup">{icon get="cup_edit"}</a>
			{/if}
			<span class="dikgedrukt">{$maaltijd->getTitel()} op {$maaltijd->datum|date_format:"%A %e %B"} om {$maaltijd->tijd|date_format:"%H:%M"}</span>
		</p>
		{if $maaltijd->verwijderd}
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
	<br />
	{*
	<a href="/corvee/beheer/indelen" title="Leden automatisch indelen voor taken" class="btn">{icon get="date"} Automatisch indelen</a>
	<a href="/corvee/beheer/herinneren" title="Verstuur herinneringen" class="btn">{icon get="clock"} Herinneringen versturen</a>
	*}
	<div class="float-right">
		{if !isset($maaltijd) OR !$maaltijd->verwijderd}
			<a class="btn" onclick="$(this).hide(); window.maalcie.takenShowOld();">{icon get="eye"} Toon verleden</a>
			<a href="/corvee/beheer/prullenbak" class="btn">{icon get="bin_closed"} Open prullenbak</a>
			<a href="/corvee/beheer/nieuw{if isset($maaltijd)}/{$maaltijd->maaltijd_id}{/if}" class="btn post popup">{icon get="add"} Nieuwe taak</a>
		{/if}
	</div>
{/if}
{if isset($repetities) and (!isset($maaltijd) or !$maaltijd->verwijderd)}
	<form action="/corvee/beheer/nieuw{if isset($maaltijd)}/{$maaltijd->maaltijd_id}{/if}" method="post" class="Formulier ModalForm SubmitReset">
		{printCsrfField()}
		<label for="crid" style="width: auto;">{icon get="calendar_add"} Periodieke taken aanmaken:</label>&nbsp;
		<select id="crid" name="crv_repetitie_id" value="kies" origvalue="kies" class="FormElement SubmitChange">
			<option selected="selected">kies</option>
			{foreach from=$repetities item=repetitie}
				<option value="{$repetitie->crv_repetitie_id}">{$repetitie->getCorveeFunctie()->naam} op {$repetitie->getDagVanDeWeekText()}</option>
			{/foreach}
		</select>
		<a href="/corvee/repetities" class="btn" title="Periodiek corvee beheren">{icon get="calendar_edit"}</a>
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
