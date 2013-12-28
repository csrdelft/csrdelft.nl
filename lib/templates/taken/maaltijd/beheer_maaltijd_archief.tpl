{*
	beheer_maaltijd_archief.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}">
	<td>{$maaltijd->getDatum()|date_format:"%d-%m-%Y"} {$maaltijd->getTijd()|date_format:"%H:%M"}</td>
	<td>{$maaltijd->getTitel()}</td>
	<td>&euro; {$maaltijd->getPrijs()|string_format:"%.2f"}</td>
	<td>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()}" style="display: inline-block;">
			<a title="Toon aanmeldingen" class="knop" onclick="$('div.aanmeldingen-{$maaltijd->getMaaltijdId()}').toggle();">{icon get="eye"} Toon <strong>{$maaltijd->getAantalAanmeldingen()}</strong></a>
		</div>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()}" style="display: none;">
			{$maaltijd->getAanmeldingenFormatted()}
		</div>
	</td>
</tr>
{/strip}