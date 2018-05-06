{*
	mijn_vrijstelling.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<h3>Corveevrijstelling</h3>
{if $vrijstelling === false}
<p>U heeft geen vrijstelling.</p>
{else}
<p>
In de onderstaande tabel staat de vrijstelling die u heeft gekregen.
</p>
<table class="maalcie-tabel" style="width: 650px;">
	<thead>
		<tr>
			<th>Van</th>
			<th>Tot</th>
			<th>Percentage</th>
			<th>Punten</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>{$vrijstelling->begin_datum|date_format:"%e %b %Y"}</td>
			<td>{$vrijstelling->eind_datum|date_format:"%e %b %Y"}</td>
			<td>{$vrijstelling->percentage}%</td>
			<td>{$vrijstelling->getPunten()}</td>
		</tr>
	</tbody>
</table>
{/if}