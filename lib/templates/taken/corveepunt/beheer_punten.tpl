{*
	beheer_punten.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u voor alle leden de corveepunten beheren.
</p>
<p>
De onderstaande tabel bevat een overzicht van de punten die per corveefunctie zijn verdiend.
Tussen haakjes staat het aantal keer dat het lid is ingedeeld voor de betreffende functie.
De kolom punten is exclusief bonus/malus.
De kolom prognose geeft aan hoeveel punten het lid totaal zal hebben na het uitvoeren van de corveetaken waarvoor hij/zij is ingedeeld (inclusief bonus/malus-punten).
</p>
<a href="{$module}/resetjaar" title="Reset corveejaar" class="knop get confirm">{icon get="lightning"} Corveejaar resetten</a>
<table id="taken-tabel" class="taken-tabel">
{foreach name=tabel from=$matrix item=puntenlijst}
	{if $smarty.foreach.tabel.index % 25 === 0}
		{if !$smarty.foreach.tabel.first}</tbody>{/if}
	<thead>
		<tr>
			<th>Lid</th>
		{foreach from=$functies item=functie}
			<th title="{$functie->getNaam()}">{$functie->getAfkorting()}</th>
		{/foreach}
			<th>Punten</th>
			<th>Bonus<br />/malus</th>
			<th>Prognose</th>
		</tr>
	</thead>
	<tbody>
	{/if}
	{include file='taken/corveepunt/beheer_punten_lijst.tpl' puntenlijst=$puntenlijst}
{/foreach}
	</tbody>
</table>