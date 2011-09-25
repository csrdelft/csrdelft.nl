{assign var='actief' value='abonnementenbeheer'}
{include file='maaltijdketzer/menu.tpl'}

<h1>Maaltijdabonnementen-beheer</h1>
<p>
Hier kunt u abonnementen aan en uitzetten. <b>Wijzigingen worden direct opgeslagen</b>
<table id="abolijst" class="abolijst">
	<thead>
		<tr><th>Naam</th><th><span title="waarschuwing">&#916;</span></th><th>Status</th><th>Jaar</th><th>Verticale</th><th>Maandag</th><th>Donderdag</th><th>Verticale</th><th>Achternaam</th></tr>
	</thead>
	<tbody>
	{foreach from=$leden item=lid}
		<tr class="abo {if !($lid.status=='S_NOVIET' OR $lid.status=='S_GASTLID' OR $lid.status=='S_LID')}geenlid{/if}">
			<td>{$lid.uid|csrnaam:'civitas'}</td>
			<td>
				{if !($lid.status=='S_NOVIET' OR $lid.status=='S_GASTLID' OR $lid.status=='S_LID')}
					<img src="{icon get="fout" notag=true}" alt="Geen lid (meer)!" title="Geen lid (meer)!" />
				{/if}&nbsp;
			</td>
			<td>{$lid.status}</td>
			<td>{$lid.lidjaar}</td>
			<td>{$lid.verticalenaam}</td>
			<td id="{$lid.uid}-A_MAANDAG" class="abovinkje"><input type="checkbox" name="abo_ma" value="1" {if $lid.abos.maandag}checked="checked"{/if} /></td>
			<td id="{$lid.uid}-A_DONDERDAG" class="abovinkje"><input type="checkbox" name="abo_do" value="1" {if $lid.abos.donderdag}checked="checked"{/if} /></td>
			<td id="{$lid.uid}-{$lid.abos.verticaleabonaam}" class="abovinkje"><input type="checkbox" name="abo_ve" value="1" {if $lid.abos.verticale}checked="checked"{/if} /></td>
			<td>{$lid.achternaam}</td>
		</tr>
	{foreachelse}
		<tr><td colspan=5>Geen gegevens vonden.</td></tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Naam</th><th><span title="waarschuwing">&#916;</span></th><th>Status</th><th>Jaar</th><th>Verticale</th><th>Maandag</th><th>Donderdag</th><th>Verticale</th><th>Achternaam</th></tr>
	</tfoot>
</table>

