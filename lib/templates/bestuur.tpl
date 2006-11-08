<h1>Bestuur {$bestuur.naam}&nbsp;{$bestuur.jaar}-{$bestuur.jaar+1}</h1>

<div id="bestuurContainer" style="text-align: center">
	<img src="{$csr_pics}bestuur/{$bestuur.jaar}.jpg" alt="bestuursfoto" />
	<table class="bestuur" style="">
		<tr>
			<td><a href="/intern/profiel/{$bestuur.vice_praeses_uid}">{$bestuur.vice_praeses}</a></td>		
			<td><a href="/intern/profiel/{$bestuur.abactis_uid}">{$bestuur.abactis}</a></td>
			<td><a href="/intern/profiel/{$bestuur.praeses_uid}">{$bestuur.praeses}</a></td>
			<td><a href="/intern/profiel/{$bestuur.fiscus_uid}">{$bestuur.fiscus}</a></td>
			<td><a href="/intern/profiel/{$bestuur.vice_abactis_uid}">{$bestuur.vice_abactis}</a></td>
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
