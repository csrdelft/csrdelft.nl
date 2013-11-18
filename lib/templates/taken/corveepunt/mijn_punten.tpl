{*
	mijn_punten.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<h2>Corveepunten</h2>
<p>
In de onderstaande tabel is een overzicht te vinden van de punten die u per corveefunctie heeft verdiend.
Tussen haakjes staat het aantal keer dat u bent ingedeeld in deze functie.
De prognose geeft aan hoeveel punten u totaal zal hebben na het uitvoeren van de corveetaken waarvoor u bent ingedeeld (inclusief bonuspunten).
</p>
<table class="taken-tabel" style="width: 350px;">
	<thead>
		<tr>
			<th>Functie</th>
			<th>Punten</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$puntenlijst.aantal key=fid item=aantal}
		<tr>
			<td>{$functies[$fid]->getNaam()} ({$aantal})</th>
			<td>{$puntenlijst.punten[$fid]}{if $puntenlijst.bonus[$fid] > 0}+{/if}{if $puntenlijst.bonus[$fid] !== 0}{$puntenlijst.bonus[$fid]}{/if}</td>
		</tr>
{/foreach}
		<tr><td style="font-weight: bold;">Totaal</td><td>{$puntenlijst.puntenTotaal}{if $puntenlijst.bonusTotaal > 0}+{/if}{if $puntenlijst.bonusTotaal !== 0}{$puntenlijst.bonusTotaal}{/if}</td></tr>
		<tr><td style="font-weight: bold;">Prognose</td><td style="background-color: #{$puntenlijst.prognoseColor}">{$puntenlijst.prognose}</td></tr>
	</tbody>
</table>