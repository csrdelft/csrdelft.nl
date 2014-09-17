{*
	mijn_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}"{if !$aanmelding and $maaltijd->getIsGesloten()} class="taak-grijs"{/if}>
	<td>
		{$maaltijd->getDatum()|date_format:"%a %e %b"} {$maaltijd->getTijd()|date_format:"%H:%M"}
{assign var=corvee value=$maaltijd->magMaaltijdlijstTonen()}
{if $corvee|is_a:'\CorveeTaak'}
		<div style="float: right;">
			{icon get="paintcan" title=$corvee->getCorveeFunctie()->naam}
		</div>
{/if}
	</td>
	<td>{$maaltijd->getTitel()}
		<div style="float: right;">
{assign var=prijs value=$maaltijd->getPrijs()|string_format:"%.2f"}
{if $aanmelding and $aanmelding->getSaldoStatus() < 0}
		{icon get="money_delete" title="U staat rood bij de MaalCie!&#013;Maaltijdprijs: &euro; "|cat:$prijs}
{elseif $aanmelding and $aanmelding->getSaldoStatus() < 2}
		{icon get="money_delete" title="Uw MaalCie saldo is te laag!&#013;Maaltijdprijs: &euro; "|cat:$prijs}
{elseif $prijs !== $standaardprijs}
		{icon get="money" title="Afwijkende maaltijdprijs: &euro; "|cat:$prijs}
{else}
		{icon get="money_euro" title="Maaltijdprijs: &euro; "|cat:$prijs}
{/if}
		</div>
	</td>
	<td style="text-align: center;">
		{$maaltijd->getAantalAanmeldingen()} ({$maaltijd->getAanmeldLimiet()})
{if $corvee}
		<div style="float: right;">
			<a href="/maaltijdenlijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst" class="knop">{icon get="table"}</a>
		</div>
{/if}
	</td>
{if $aanmelding}
	{if $maaltijd->getIsGesloten()}
	<td class="maaltijd-aangemeld">
		Ja
		{if $aanmelding->getDoorAbonnement()} (abo){/if}
		<div style="float: right;">
			{assign var=date value=$maaltijd->getLaatstGesloten()|date_format:"%H:%M"}
			{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
		</div>
	{else}
	<td class="maaltijd-aangemeld">
		<a href="{Instellingen::get('taken', 'url')}/afmelden/{$maaltijd->getMaaltijdId()}" class="knop post maaltijd-aangemeld"><input type="checkbox" checked="checked" /> Ja</a>
		{if $aanmelding->getDoorAbonnement()} (abo){/if}
	{/if}
	</td>
	<td class="maaltijd-gasten">
	{if $maaltijd->getIsGesloten()}
		{$aanmelding->getAantalGasten()}
	{else}
		<form action="{Instellingen::get('taken', 'url')}/gasten/{$maaltijd->getMaaltijdId()}" method="post" class="Formulier InlineForm">
			<div class="InlineFormToggle maaltijd-gasten">{$aanmelding->getAantalGasten()}</div>
			<div class="InputField">
				<input type="text" name="aantal_gasten" value="{$aanmelding->getAantalGasten()}" origvalue="{$aanmelding->getAantalGasten()}" class="FormElement" maxlength="4" size="4" />
			</div>
			<div class="FormButtons">
				<a class="knop submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="knop reset cancel" title="Annuleren">{icon get="delete"}</a>
			</div>
		</form>
	{/if}
	</td>
	<td>
	{if $maaltijd->getIsGesloten()}
		{if $aanmelding->getGastenEetwens()}
			{icon get="comment" title=$aanmelding->getGastenEetwens()}
		{/if}
	{else}
	{if $aanmelding->getAantalGasten() > 0}
		<form action="{Instellingen::get('taken', 'url')}/opmerking/{$maaltijd->getMaaltijdId()}" method="post" class="Formulier InlineForm">
			<div class="InlineFormToggle" title="{$aanmelding->getGastenEetwens()}">
		{if $aanmelding->getGastenEetwens()}
				<a class="knop">{icon get="comment_edit" title=$aanmelding->getGastenEetwens()}</a>
		{else}
				<a class="knop">{icon get="comment_add" title="Gasten allergie/diëet"}</a>
		{/if}
			</div>
			<div class="InputField">
				<input type="text" name="gasten_eetwens" value="{$aanmelding->getGastenEetwens()}" origvalue="{$aanmelding->getGastenEetwens()}" class="FormElement" maxlength="255" size="20" />
			</div>
			<div class="FormButtons">
				<a class="knop submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
				<a class="knop reset cancel" title="Annuleren">{icon get="delete"}</a>
			</div>
		</form>
	{/if}
	{/if}
	</td>
{else}
	{if $maaltijd->getIsGesloten() or $maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()}
	<td class="maaltijd-afgemeld">
		{if !$maaltijd->getIsGesloten() and $maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()}
			{icon get="stop" title="Maaltijd is vol"}&nbsp;
		{/if}
		Nee
		{if $maaltijd->getIsGesloten()}
		<span style="float: right;">
			{assign var=date value=$maaltijd->getLaatstGesloten()|date_format:"%H:%M"}
			{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
		</span>
		{/if}
	{else}
	<td class="maaltijd-afgemeld">
		<a href="{Instellingen::get('taken', 'url')}/aanmelden/{$maaltijd->getMaaltijdId()}" class="knop post maaltijd-afgemeld"><input type="checkbox" /> Nee</a>
	{/if}
	</td>
	<td>-</td>
	<td></td>
{/if}
</tr>
{/strip}