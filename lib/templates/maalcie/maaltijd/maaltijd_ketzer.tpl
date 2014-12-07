{* maaltijd_ketzer.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
{strip}
	<div class="bb-block bb-maaltijd" id="maaltijdketzer-{$maaltijd->getMaaltijdId()}">
		{if LoginModel::mag('P_LOGGED_IN')}
			<div class="aanmelddata maaltijd-{if $aanmelding}aan{else}af{/if}gemeld">Aangemeld:<br />

				{if !$maaltijd->getIsGesloten() && LoginModel::mag('P_MAAL_IK')}

					{if $aanmelding}
						<a onclick="ketzer_ajax('/maaltijdenketzer/afmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');" class="btn maaltijd-aangemeld"><input type="checkbox" checked="checked" /> Ja</a>

					{elseif $maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()}
						{icon get="stop" title="Maaltijd is vol"}&nbsp;
						<span class="maaltijd-afgemeld">Nee</span>

					{else}
						<a onclick="ketzer_ajax('/maaltijdenketzer/aanmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');" class="btn maaltijd-afgemeld"><input type="checkbox" /> Nee</a>

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

				{if $maaltijd->getIsGesloten()}&nbsp;
					{assign var=date value=$maaltijd->getLaatstGesloten()|date_format:"%H:%M"}
					{icon get="lock" title="Maaltijd is gesloten om "|cat:$date}
				{/if}

			</div>
		{/if}
		<div class="maaltijdgegevens">
			<div class="titel"><a href="/maaltijdenketzer">{$maaltijd->getTitel()}</a></div>
			 van {$maaltijd->getDatum()|date_format:"%A %e %B"} {$maaltijd->getTijd()|date_format:"%H:%M"}
			{if $maaltijd->getPrijs() !== $standaardprijs}
				&nbsp; (&euro; {$maaltijd->getPrijsFloat()|string_format:"%.2f"})
			{/if}
			{if $maaltijd->magBekijken(LoginModel::getUid())}
				<div class="float-right">
					{icon get="paintcan" title=$maaltijd->maaltijdcorvee->getCorveeFunctie()->naam}
				</div>
			{/if}
			<div class="small">
				{if $maaltijd->magSluiten(LoginModel::getUid())}
					<a href="/maaltijdenlijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst">
				{/if}
				Inschrijvingen: <em>{$maaltijd->getAantalAanmeldingen()}</em> van <em>{$maaltijd->getAanmeldLimiet()}</em>
				{if $maaltijd->magSluiten(LoginModel::getUid())}
					</a>
				{/if}
			</div>
		</div>
	</div>
{/strip}