{assign var='actief' value='abonnementenbeheer'}
{include file='maaltijdketzer/menu.tpl'}

<h1>Maaltijdabonnementen-beheer</h1>
<p>
	Hier kunt u abonnementen aan en uitzetten.<br/> 
	<b>Wijzigingen worden direct opgeslagen</b>
</p>
<label for="filterwaarschuwingen">Geef alleen waarschuwingen weer</label><input type="checkbox" name="filter" id="filterwaarschuwingen" value="1" checked="checked" /><br/>

<table id="abolijst" class="abolijst">
	<thead>
		<tr><th>Naam</th><th><span title="waarschuwing">&#916;</span></th><th>Jaar</th><th>Donderdag</th><th>Verticale</th><th>Verticale</th><th>Achternaam</th></tr>
	</thead>
	<tbody>
	{foreach from=$leden item=lid}
		<tr class="abo {if !($lid.status=='S_NOVIET' OR $lid.status=='S_GASTLID' OR $lid.status=='S_LID')}geenlid{elseif $lid.kring==0 AND $lid.abos.verticale}geenkring{/if}">
			<td>{$lid.uid|csrnaam:'civitas'}</td>
			<td>
				{if !($lid.status=='S_NOVIET' OR $lid.status=='S_GASTLID' OR $lid.status=='S_LID')}
					<img src="{icon get="fout" notag=true}" alt="Geen lid!" title="Geen lid. Lidstatus: {$lid.status}{if $lid.kring==0 AND $lid.abos.verticale} én geen actief kringlid (kring.0){/if}" />{elseif $lid.kring==0 AND $lid.abos.verticale}<img src="{icon get="fout" notag=true}" alt="Kring.0" title="Geen actief kringlid (kring.0)" />{/if}&nbsp;
			</td>
			<td>{$lid.lidjaar}</td>
			<td id="{$lid.uid}-A_DONDERDAG" class="abovinkje"><input type="checkbox" name="abo_do" value="1" {if $lid.abos.donderdag}checked="checked"{/if} /></td>
			<td>{$lid.verticalenaam}</td>
			<td id="{$lid.uid}-{$lid.abos.verticaleabonaam}" class="abovinkje"><input type="checkbox" name="abo_ve" value="1" {if $lid.abos.verticale}checked="checked"{/if} /></td>
			<td>{$lid.achternaam}</td>
		</tr>
	{foreachelse}
		<tr><td colspan=5>Geen gegevens gevonden.</td></tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Naam</th><th><span title="waarschuwing">&#916;</span></th><th>Jaar</th><th>Donderdag</th><th>Verticale</th><th>Verticale</th><th>Achternaam</th></tr>
	</tfoot>
</table>
