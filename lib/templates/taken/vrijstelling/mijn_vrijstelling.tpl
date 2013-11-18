{*
	mijn_vrijstelling.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<h2>Corveevrijstelling</h2>
{if $vrijstelling === null}
<p>U heeft geen vrijstelling.</p>
{else}
<p>
In de onderstaande tabel staat de vrijstelling die u heeft gekregen.
</p>
<table class="taken-tabel">
	<thead>
		<tr>
			<th>Van</th>
			<th>Tot</th>
			<th>Percentage</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
			<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
			<td>{$vrijstelling->getPercentage()}%</td>
		</tr>
	</tbody>
</table>
{/if}