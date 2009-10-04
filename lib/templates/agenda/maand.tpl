<h1>Agenda {$maand} {$jaar}</h1>
<table class="agenda maand">
	<a class="knop">&laquo; Vorige maand</a></td>	
	<a class="knop">Volgende maand &raquo;</a>
	<br /><br />
	<tr>
		<th> </th>
		<th>Zondag</th>
		<th>Maandag</th>
		<th>Dinsdag</th>
		<th>Woensdag</th>
		<th>Donderdag</th>
		<th>Vrijdag</th>
		<th>Zaterdag</th>
	</tr>
	{foreach from=$weken key=weeknr item=dagen}
		<tr>		
			<th>{$weeknr}</th>
			{foreach from=$dagen key=dagnr item=items}
				<td>
					{$dagnr}					
					{foreach from = $items item=item}
						<hr />
						{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"} 
						<b>{$item->getTitel()}</b>
						<br />
					{/foreach}
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>