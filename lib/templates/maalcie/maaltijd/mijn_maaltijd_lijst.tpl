{* mijn_maaltijd_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{strip}
	<tr id="maaltijd-row-{$maaltijd->maaltijd_id}"{if $maaltijd->aanmeld_limiet === 0 or ($maaltijd->gesloten and ! $aanmelding)} class="taak-grijs"{/if}>
		<td>
			{$maaltijd->datum|date_format:"%a %e %b"} {$maaltijd->tijd|date_format:"%H:%M"}
			{if $maaltijd->magBekijken(CsrDelft\model\security\LoginModel::getUid())}
				<div class="float-right">
					{icon get="paintcan" title=$maaltijd->maaltijdcorvee->getCorveeFunctie()->naam}
				</div>
			{/if}
		</td>
		<td>
			<div class="titel">{$maaltijd->titel}
				<span title="BB-code: [maaltijd={$maaltijd->maaltijd_id}]" class="maaltijd-id"> (#{$maaltijd->maaltijd_id})</span>
				<div class="float-right">
					{assign var=prijs value=$maaltijd->getPrijsFloat()|string_format:"%.2f"}
					{if $aanmelding and $aanmelding->getSaldoStatus() < 0}
						{icon get="money_delete" title="U hebt een negatief CiviSaldo!&#013;Maaltijdprijs: &euro; "|cat:$prijs}
					{elseif $aanmelding and $aanmelding->getSaldoStatus() < 2}
						{icon get="money_delete" title="Uw CiviSaldo is te laag!&#013;Maaltijdprijs: &euro; "|cat:$prijs}
					{elseif $maaltijd->getPrijs() != $standaardprijs}
						{icon get="money" title="Afwijkende maaltijdprijs: &euro; "|cat:$prijs}
					{else}
						{icon get="money_euro" title="Maaltijdprijs: &euro; "|cat:$prijs}
					{/if}
				</div>
			</div>
			{$maaltijd->omschrijving|bbcode}
		</td>
		<td class="text-center">
			{$maaltijd->getAantalAanmeldingen()} ({$maaltijd->aanmeld_limiet})
			{if $maaltijd->magSluiten(CsrDelft\model\security\LoginModel::getUid())}
				<div class="float-right">
					<a href="/maaltijden/lijst/{$maaltijd->maaltijd_id}" title="Toon maaltijdlijst" class="btn">{icon get="table"}</a>
				</div>
			{/if}
		</td>
		{if $aanmelding}
			{if $maaltijd->gesloten}
				<td class="maaltijd-aangemeld">
					Ja
					{if $aanmelding->door_abonnement} (abo){/if}
					<div class="float-right">
						{assign var=date value=$maaltijd->laatst_gesloten|date_format:"%H:%M"}
						{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
					</div>
				{else}
				<td class="maaltijd-aangemeld">
					<a href="/maaltijden/ketzer/afmelden/{$maaltijd->maaltijd_id}" class="btn post maaltijd-aangemeld"><input type="checkbox" checked="checked" /> Ja</a>
					{if $aanmelding->door_abonnement} (abo){/if}
				{/if}
			</td>
			<td class="maaltijd-gasten">
				{if $maaltijd->gesloten}
					{$aanmelding->aantal_gasten}
				{else}
					<div class="InlineForm">
						<div class="InlineFormToggle maaltijd-gasten">{$aanmelding->aantal_gasten}</div>
						<form action="/maaltijden/ketzer/gasten/{$maaltijd->maaltijd_id}" method="post" class="Formulier InlineForm ToggleForm">
							{printCsrfField()}
							<input type="text" name="aantal_gasten" value="{$aanmelding->aantal_gasten}" origvalue="{$aanmelding->aantal_gasten}" class="FormElement" maxlength="4" size="4" />
							<a class="btn submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
							<a class="btn reset cancel" title="Annuleren">{icon get="delete"}</a>
						</form>
					</div>
				{/if}
			</td>
			<td>
				{if $maaltijd->gesloten}
					{if $aanmelding->gasten_eetwens}
						{icon get="comment" title=$aanmelding->gasten_eetwens}
					{/if}
				{else}
					{if $aanmelding->aantal_gasten > 0}
						<div class="InlineForm">
							<div class="InlineFormToggle" title="{$aanmelding->gasten_eetwens}">
								{if $aanmelding->gasten_eetwens}
									<a class="btn">{icon get="comment_edit" title=$aanmelding->gasten_eetwens}</a>
								{else}
									<a class="btn">{icon get="comment_add" title="Gasten allergie/diëet"}</a>
								{/if}
							</div>
							<form action="/maaltijden/ketzer/opmerking/{$maaltijd->maaltijd_id}" method="post" class="Formulier InlineForm ToggleForm">
								{printCsrfField("/maaltijden/ketzer/opmerking/{$maaltijd->maaltijd_id}")}
								<input type="text" name="gasten_eetwens" value="{$aanmelding->gasten_eetwens}" origvalue="{$aanmelding->gasten_eetwens}" class="FormElement" maxlength="255" size="20" />
								<a class="btn submit" title="Wijzigingen opslaan">{icon get="accept"}</a>
								<a class="btn reset cancel" title="Annuleren">{icon get="delete"}</a>
							</form>
						</div>
					{/if}
				{/if}
			</td>
		{else}
			{if $maaltijd->gesloten or $maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet}
				<td class="maaltijd-afgemeld">
					{if !$maaltijd->gesloten and $maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet}
						{icon get="stop" title="Maaltijd is vol"}&nbsp;
					{/if}
					Nee
					{if $maaltijd->gesloten}
						<span class="float-right">
							{assign var=date value=$maaltijd->laatst_gesloten|date_format:"%H:%M"}
							{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
						</span>
					{/if}
				{else}
				<td class="maaltijd-afgemeld">
					<a href="/maaltijden/ketzer/aanmelden/{$maaltijd->maaltijd_id}" class="btn post maaltijd-afgemeld"><input type="checkbox" /> Nee</a>
					{/if}
			</td>
			<td>-</td>
			<td></td>
		{/if}
	</tr>
{/strip}
