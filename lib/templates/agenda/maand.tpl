<h1>Agenda {$datum|date_format:"%B %Y"}</h1>
<table class="agenda maand">
	<a class="knop" href="{$urlVorige}" style="float: left;" >&laquo; Vorige maand</a></td>
	<a class="knop" href="{$urlVolgende}" style="float: right;">Volgende maand &raquo;</a>
	<br /><br style="clear: both;" />
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
					{if	$magToevoegen}
						<a class="knop">+</a>
					{/if}	
					{$dagnr}	
					{foreach from=$items item=item}
						<hr style="clear: both;" />
						{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"} 
						<b>{$item->getTitel()}</b>
						<br />
					{/foreach}
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>