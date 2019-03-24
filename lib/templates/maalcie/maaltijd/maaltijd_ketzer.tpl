{* maaltijd_ketzer.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{strip}
	<div class="bb-block bb-maaltijd maaltijdketzer-{$maaltijd->maaltijd_id}" data-maaltijdnaam="{$maaltijd->titel}">
		{toegang P_LOGGED_IN}
			<div class="aanmelddata maaltijd-{if $aanmelding}aan{else}af{/if}gemeld">Aangemeld:<br />

				{if !$maaltijd->gesloten && CsrDelft\model\security\LoginModel::mag(P_MAAL_IK)}

					{if $aanmelding}
						<a onclick="window.ketzerAjax('/maaltijdenketzer/afmelden/{$maaltijd->maaltijd_id}', '.maaltijdketzer-{$maaltijd->maaltijd_id}');" class="btn maaltijd-aangemeld" tabindex="0"><input type="checkbox" checked="checked" /> Ja</a>

					{elseif $maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet}
						{icon get="stop" title="Maaltijd is vol"}&nbsp;
						<span class="maaltijd-afgemeld">Nee</span>

					{else}
						<a onclick="window.ketzerAjax('/maaltijdenketzer/aanmelden/{$maaltijd->maaltijd_id}', '.maaltijdketzer-{$maaltijd->maaltijd_id}');" class="btn maaltijd-afgemeld" tabindex="0"><input type="checkbox" /> Nee</a>

					{/if}

				{else}

					{if $aanmelding}
						<span class="maaltijd-aangemeld">Ja{if $aanmelding->door_abonnement} (abo){/if}</span>
					{else}
						<span class="maaltijd-afgemeld">Nee</span>
					{/if}

				{/if}

				{if $aanmelding and $aanmelding->aantal_gasten > 0}
					+{$aanmelding->aantal_gasten}
				{/if}

				{if $aanmelding and $aanmelding->gasten_eetwens}
					{icon get="comment" title=$aanmelding->gasten_eetwens}
				{/if}

				{if $maaltijd->gesloten}&nbsp;
					{assign var=date value=$maaltijd->laatst_gesloten|date_format:"%H:%M"}
					{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
				{/if}

			</div>
		{/toegang}
		<div class="maaltijdgegevens">
			<div class="titel">
				<a href="/maaltijdenketzer">{$maaltijd->titel}</a>
				{if $maaltijd->getPrijs() !== $standaardprijs}
					&nbsp; (&euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"})
				{/if}
			</div>
			op {$maaltijd->datum|date_format:"%A %e %B"} om {$maaltijd->tijd|date_format:"%H:%M"}
			{if $maaltijd->magBekijken(CsrDelft\model\security\LoginModel::getUid())}
				<div class="float-right">
					{icon get="paintcan" title=$maaltijd->maaltijdcorvee->getCorveeFunctie()->naam}
				</div>
			{/if}
			<div class="small">
				{if $maaltijd->magSluiten(CsrDelft\model\security\LoginModel::getUid())}
					<a href="/maaltijdenlijst/{$maaltijd->maaltijd_id}" title="Toon maaltijdlijst">
				{/if}
				Inschrijvingen: <em>{$maaltijd->getAantalAanmeldingen()}</em> van <em>{$maaltijd->aanmeld_limiet}</em>
				{if $maaltijd->magSluiten(CsrDelft\model\security\LoginModel::getUid())}
					</a>
				{/if}
			</div>
			{$maaltijd->omschrijving|bbcode}
		</div>
	</div>
{/strip}
