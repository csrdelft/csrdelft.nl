<!DOCTYPE html>
<html>{strip}
	<head>
		<title>{$titel} {$maaltijd->getDatum()|date_format:"%A %e %B"}</title>
		{foreach from=$stylesheets item=sheet}
			<link rel="stylesheet" href="{$sheet}" type="text/css" />
		{/foreach}
		{foreach from=$scripts item=script}
			<script type="text/javascript" src="{$script}"></script>
		{/foreach}
	</head>
	<body>
		<a href="/" style="float: right;"><img alt="Beeldmerk van de Vereniging" src="{$CSR_PICS}/layout/beeldmerk.jpg" /></a>
		<h1>{$titel} op {$maaltijd->getDatum()|date_format:"%A %e %B %Y"}</h1>
		<div class="header">{Instellingen::get('maaltijden', 'maaltijdlijst_tekst')|replace:'MAALTIJDPRIJS':$prijs}</div>
		{if !$maaltijd->getIsGesloten()}
			<h1 id="gesloten-melding" style="color: red">De maaltijd is nog niet gesloten
				{if !$maaltijd->getIsVerwijderd() and !$maaltijd->getIsGesloten()}
					&nbsp;<button href="{Instellingen::get('taken', 'url')}/sluit/{$maaltijd->getMaaltijdId()}" class="knop post confirm" title="Het sluiten van de maaltijd betekent dat niemand zich meer kan aanmelden voor deze maaltijd">Inschrijving sluiten</button>
				{/if}
			</h1>
		{/if}
		{if $maaltijd->getAantalAanmeldingen() > 0}
			<table class="container">
				<tr>
					{foreach from=$aanmeldingen item="tabel" name="tabellen"}
						<td>
							<table class="aanmeldingen">
								<tbody>
									{foreach from=$tabel item="aanmelding"}
										<tr>
											{if $aanmelding->getUid()}
												<td{if $aanmelding->getUid() == 1017 || $aanmelding->getUid() == 1345} style="font-weight: bold; font-style: italic; text-decoration: underline !important; text-transform: uppercase;"{/if}>{Lid::naamLink($aanmelding->getUid(), Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'))}
													<br />
													{assign var=eetwens value=LidCache::getLid($aanmelding->getUid())->getProperty('eetwens')}
													{if $eetwens !== ''}
														<span class="eetwens">
															{$eetwens}
														</span>
													{/if}
													{if $aanmelding->getGastenEetwens() !== ''}
														<span class="opmerking">Gasten: </span>
														<span class="eetwens">
															{$aanmelding->getGastenEetwens()}
														</span>
													{/if}
												</td>
												<td class="saldo">{$aanmelding->getSaldoMelding()}</td>
											{elseif $aanmelding->getDoorUid()}
												<td>Gast van {Lid::naamLink($aanmelding->getDoorUid(), Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), 'plain')}</td>
												<td class="saldo">-</td>
											{else}
												<td style="line-height: 2.2em;">&nbsp;</td>
												<td class="saldo"></td>
											{/if}
											<td class="geld">&euro;</td>
											<td class="box">m</td>
										</tr>
									{/foreach}
							</table>
							{if !$smarty.foreach.tabellen.last}
								<div style="display: inline-block; width: 20px;"></div>
							{/if}
						</td>
					{/foreach}
				</tr>
			</table>
		{else}
			<p>Nog geen aanmeldingen voor deze maaltijd.</p>
		{/if}
		<table class="container">
			<tr>
				<td class="maaltijdgegevens">
					<h3>Maaltijdgegevens</h3>
					<table>
						<tr><td>Inschrijvingen:</td><td>{$maaltijd->getAantalAanmeldingen()}</td></tr>
						<tr><td>Marge:</td><td>{$maaltijd->getMarge()}</td></tr>
						<tr><td>Eters:</td><td>{$eterstotaal}</td></tr>
						<tr><td>Budget koks:</td><td>&euro; {$maaltijd->getBudget()|string_format:"%.2f"}</td></tr>
					</table>
				</td>
				<td></td>
				<td class="corvee">
					<h3>Corvee</h3>
					{if $corveetaken}
						{table_foreach from=$corveetaken inner=rows item=taak table_attr='class="corveetaken"' cols=2 name=corveetaken}
						&bullet;&nbsp;
						{if $taak->getUid()}
							{Lid::naamLink($taak->getUid(), Instellingen::get('maaltijden', 'weergave_ledennamen_maaltijdlijst'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'))}
						{else}
							<i>vacature</i>
						{/if}
						&nbsp;({$taak->getCorveeFunctie()->naam})
						{/table_foreach}
					{else}
						<p>Geen corveetaken voor deze maaltijd in het systeem.</p>
					{/if}
				</td>
			</tr>
		</tbody>
	</table>
</body>
{/strip}</html>