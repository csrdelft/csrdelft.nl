{*
	mijn_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>Deze pagina toont een overzicht van uw aankomende corveetaken, corveepunten en vrijstellingen.
Voor vragen kunt u contact opnemen met de CorveeCaesar: <a href="mailto:corvee@csrdelft.nl">corvee@csrdelft.nl</a>.</p>
<h3>Corveerooster</h3>
{if empty($rooster)}
<p>U bent nog niet ingedeeld.</p>
{else}
<p>
De onderstaande tabel toont de aankomende corveetaken waarvoor u bent ingedeeld.
Als u niet kunt op de betreffende datum bent u zelf verantwoordelijk voor het regelen van een vervanger en dit te melden aan de <a href="mailto:corvee@csrdelft.nl">CorveeCaesar</a>.
</p>
<p>
Tip: zoek in het <a href="/corvee/rooster" title="Corveerooster">corveerooster</a> iemand met dezelfde taak wanneer u zelf wel kunt om te ruilen.
</p>
{include file='maalcie/corveetaak/corvee_rooster.tpl' mijn=true}
{/if}
