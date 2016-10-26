{* maaltijd_ketzer.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{strip}
	<div class="bb-block bb-maaltijd maaltijdketzer-{$maaltijd->maaltijd_id}">
		{if LoginModel::mag('P_LOGGED_IN')}
			<div class="aanmelddata maaltijd-{if $aanmelding}aan{else}af{/if}gemeld">Aangemeld:<br />

				{if !$maaltijd->gesloten && LoginModel::mag('P_MAAL_IK')}

					{if $aanmelding}
						<a onclick="ketzer_ajax('/maaltijdenketzer/afmelden/{$maaltijd->maaltijd_id}', '.maaltijdketzer-{$maaltijd->maaltijd_id}');" class="btn maaltijd-aangemeld"><input type="checkbox" checked="checked" /> Ja</a>

					{elseif $maaltijd->aantal_aanmeldingen >= $maaltijd->aanmeld_limiet}
						{icon get="stop" title="Maaltijd is vol"}&nbsp;
						<span class="maaltijd-afgemeld">Nee</span>

					{else}
						<a onclick="ketzer_ajax('/maaltijdenketzer/aanmelden/{$maaltijd->maaltijd_id}', '.maaltijdketzer-{$maaltijd->maaltijd_id}');" class="btn maaltijd-afgemeld"><input type="checkbox" /> Nee</a>

					{/if}

				{else}

					{if $aanmelding}
						<span class="maaltijd-aangemeld">Ja{if $aanmelding->getDoorAbonnement()} (abo){/if}</span>
					{else}
						<span class="maaltijd-afgemeld">Nee</span>
					{/if}

				{/if}

				{if $aanmelding and $aanmelding->getAantalGasten() > 0}
					+{$aanmelding->getAantalGasten()}
				{/if}

				{if $aanmelding and $aanmelding->getGastenEetwens()}
					{icon get="comment" title=$aanmelding->getGastenEetwens()}
				{/if}

				{if $maaltijd->gesloten}&nbsp;
					{assign var=date value=$maaltijd->laatst_gesloten|date_format:"%H:%M"}
					{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
				{/if}

			</div>
		{/if}
		<div class="maaltijdgegevens">
			<div class="titel">
				<a href="/maaltijdenketzer">{$maaltijd->titel}</a>
				{if $maaltijd->prijs !== $standaardprijs}
					&nbsp; (&euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"})
				{/if}
			</div>
			op {$maaltijd->datum|date_format:"%A %e %B"} om {$maaltijd->tijd|date_format:"%H:%M"}
			{if $maaltijd->magBekijken(LoginModel::getUid())}
				<div class="float-right">
					{icon get="paintcan" title=$maaltijd->maaltijdcorvee->getCorveeFunctie()->naam}
				</div>
			{/if}
			<div class="small">
				{if $maaltijd->magSluiten(LoginModel::getUid())}
					<a href="/maaltijdenlijst/{$maaltijd->maaltijd_id}" title="Toon maaltijdlijst">
				{/if}
				Inschrijvingen: <em>{$maaltijd->aantal_aanmeldingen}</em> van <em>{$maaltijd->aanmeld_limiet}</em>
				{if $maaltijd->magSluiten(LoginModel::getUid())}
					</a>
				{/if}
			</div>
			{CsrBB::parse($maaltijd->omschrijving)}
		</div>
	</div>
{/strip}