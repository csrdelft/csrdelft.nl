{*
	mijn_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>Deze pagina toont een overzicht van uw aankomende corveetaken, corveepunten en vrijstellingen.
Voor vragen kunt u contact opnemen met de CorveeCaesar: <a href="mailto:corvee@csrdelft.nl">corvee@csrdelft.nl</a>.</p>
<h2>Corveerooster</h2>
{if empty($rooster)}
<p>U bent nog niet ingedeeld.</p>
{else}
<p>
De onderstaande tabel toont de aankomende corveetaken waarvoor u bent ingedeeld.
Als u niet kunt op de betreffende datum kunt u proberen te ruilen via het <a href="/communicatie/forum" title="Forum">forum</a>.
</p>
{include file='taken/corveetaak/corvee_rooster.tpl' mijn=true}
{/if}