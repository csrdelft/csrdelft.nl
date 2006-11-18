<h1>Bestuur {$bestuur.naam}&nbsp;{$bestuur.jaar}-{$bestuur.jaar+1}</h1>

<div id="bestuurContainer" style="text-align: center">
	<img src="{$csr_pics}bestuur/{$bestuur.jaar}.jpg" alt="bestuursfoto" />
	<table class="bestuur" style="">
		<tr>
			<td>{$bestuur.vice_praeses}</td>		
			<td>{$bestuur.abactis}</td>
			<td>{$bestuur.praeses}</td>
			<td>{$bestuur.fiscus}</td>
			<td>{$bestuur.vice_abactis}</td>
		</tr>
		<tr>
			<th>vice-praeses</th>
			<th>abactis</th>
			<th>praeses</th>
			<th>fiscus</th>
			<th>vice-abactis</th>
		</tr>
	</table>
	
	<p>
		{$bestuur.tekst}
	</p>
</div>
