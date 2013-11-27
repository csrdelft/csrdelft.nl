{*
	mijn_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}"{if !isset($aanmelding) and $maaltijd->getIsGesloten()} class="taak-grijs"{/if}>
	<td>
		{$maaltijd->getDatum()|date_format:"%a %e %b"} {$maaltijd->getTijd()|date_format:"%H:%M"}
{if $toonlijst|is_a:'\Taken\CRV\CorveeTaak'}
		<div style="float: right;">
			{icon get="paintcan" title=$toonlijst->getCorveeFunctie()->getNaam()}
		</div>
{/if}
	</td>
	<td>{$maaltijd->getTitel()}
		<div style="float: right;">
{assign var=prijs value=$maaltijd->getPrijs()|string_format:"%.2f"}
{if isset($aanmelding) and $aanmelding->getSaldoStatus() < 0}
		{icon get="money_delete" title="U staat rood bij de MaalCie!&#013;Maaltijdprijs: &euro; "|cat:$prijs}
{elseif isset($aanmelding) and $aanmelding->getSaldoStatus() < 2}
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
{if $toonlijst}
		<div style="float: right;">
			<a href="/maaltijdenlijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst" class="knop">{icon get="table"}</a>
		</div>
{/if}
	</td>
{if isset($aanmelding)}
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
		<a href="{$globals.taken_module}/afmelden/{$maaltijd->getMaaltijdId()}" class="knop post maaltijd-aangemeld"><input type="checkbox" checked="checked" /> Ja</a>
		{if $aanmelding->getDoorAbonnement()} (abo){/if}
	{/if}
	</td>
	<td>
	{if $maaltijd->getIsGesloten()}
		{$aanmelding->getAantalGasten()}
	{else}
		<div class="inline-edit" onclick="taken_toggle_hiddenform(this);">{$aanmelding->getAantalGasten()}</div>
		<form method="post" action="{$globals.taken_module}/gasten/{$maaltijd->getMaaltijdId()}" class="Formulier taken-hidden-form taken-subform">
			<input type="text" name="aantal_gasten" value="{$aanmelding->getAantalGasten()}" maxlength="4" size="4" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="taken_toggle_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
	{/if}
	</td>
	<td>
	{if $maaltijd->getIsGesloten()}
		{if $aanmelding->getGastenOpmerking()}
			{icon get="comment" title=$aanmelding->getGastenOpmerking()}
		{/if}
	{else}
		<div onclick="taken_toggle_hiddenform(this);" title="{$aanmelding->getGastenOpmerking()}">
		{if $aanmelding->getGastenOpmerking()}
			<a class="knop">{icon get="comment_edit" title=$aanmelding->getGastenOpmerking()}</a>
		{else}
			<a class="knop">{icon get="comment_add" title="Gasten opmerking maken"}</a>
		{/if}
		</div>
		<form method="post" action="{$globals.taken_module}/opmerking/{$maaltijd->getMaaltijdId()}" class="Formulier taken-hidden-form taken-subform">
			<input type="text" name="gasten_opmerking" value="{$aanmelding->getGastenOpmerking()}" maxlength="255" size="20" />
			<a onclick="$(this).parent().submit();" title="Wijzigingen opslaan" class="knop">{icon get="accept"}</a>
			<a onclick="taken_toggle_hiddenform($(this).parent());" title="Annuleren" class="knop">{icon get="delete"}</a>
		</form>
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
		<a href="{$globals.taken_module}/aanmelden/{$maaltijd->getMaaltijdId()}" class="knop post maaltijd-afgemeld"><input type="checkbox" /> Nee</a>
	{/if}
	</td>
	<td>-</td>
	<td></td>
{/if}
</tr>
{/strip}