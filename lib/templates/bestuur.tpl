{$melding}
{if $action!='edit' AND $bestuur.isAdmin}
	<div style="float: right;">
		<a href="/vereniging/bestuur/{$bestuur.jaar}/bewerken" class="knop">bewerken</a>
	</div>
{/if}
<h1>Bestuur {$bestuur.naam}&nbsp;{$bestuur.jaar}-{$bestuur.jaar+1}</h1>

<div id="bestuurContainer">
	<img src="{$csr_pics}bestuur/{$bestuur.jaar}.jpg" alt="bestuursfoto" />
	<table class="bestuur" style="">
		<tr>
			<td>{$bestuur.vice_praeses|csrnaam:'full':false}</td>		
			<td>{$bestuur.abactis|csrnaam:'full':false}</td>
			<td>{$bestuur.praeses|csrnaam:'full':false}</td>
			<td>{$bestuur.fiscus|csrnaam:'full':false}</td>
			<td>{$bestuur.vice_abactis|csrnaam:'full':false}</td>
		</tr>
		<tr>
			<th>vice-praeses</th>
			<th>abactis</th>
			<th>praeses</th>
			<th>fiscus</th>
			<th>vice-abactis</th>
		</tr>
	</table>
	{if $action=='edit'}
			<form action="/vereniging/bestuur/{$bestuur.jaar}/bewerken" method="post">
	{/if}
	
	<div id="bestuurstekst" style="margin: 40px; padding: 20px; font-size: 18px; background-color: #eee; text-align: left;">
		{if $action=='edit'}
			<textarea name="tekst" style="width: 100%; height: 100px;">{$bestuur.tekst|ubb}</textarea>
		{else}
			{$bestuur.tekst|ubb}	
		{/if}
	</div>
	<div id="bestuursverhaal">
		{if $action=='edit'}
			<textarea name="verhaal" style="width: 100%; height: 400px;">{$bestuur.verhaal}</textarea>
			<input type="submit" value="Opslaan" /> <a href="/vereniging/bestuur/{$bestuur.jaar}/" class="knop">terug</a>
		{else}
			{$bestuur.verhaal|ubb}
		{/if}
	</div>
	{if $action=='edit'}
		</form>
	{/if}
	<div style="clear: both;"></div>
</div>
