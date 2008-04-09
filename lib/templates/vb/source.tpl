<h1>Bron {$source->name} ({$source->sourceType})</h1>
<table width = "100%">
	<tr><td width= "60%">
		{if $comefrom != "-1"}
			<a href="index.php?actie=subject&id={$comefrom}">&lt;&lt;terug naar onderwerp</a>
		{/if}
		<h2>Bron</h2>
		{if $source->sourceType=="link"}
			<a href="{$source->link}" target="_blank">{$source->link}</a>
		{/if}
		{if $source->sourceType=="discussion"}
			<a href="../forum/onderwerp/{$source->link}">{$source->name}</a>
		{/if}
		{if  $source->sourceType=="file"}
			<a href="../intern/documenten/neerladen/{$source->link}" target="_blank">{$source->name} downloaden</a>
		{/if}
		<h2>Details</h2>
		Gepost door {$source->lid} op {$source->createdate}<br/>
		Beoordeling {$source->voting()} ({$source->votecount} stemmen totaal)
		<h2>Omschrijving</h2>
		{$source->description}

	</td><td>
		<h2>Te vinden onder</h2>
		{section name=i1 loop=$source->parents}
			{if $allowedit}
				{$source->parents[i1]->geteditbuttons()}
			{/if}
			<a href="index.php?actie=subject&id={$source->parents[i1]->subjid}">{$source->parents[i1]->subjname}</a><br/>
		{/section}
		{if $allowadd}
			<a onclick="{$addlabelclick}">
				<img class="button" src="http://plaetjes.csrdelft.nl/documenten/plus.jpg"/>
				Label toevoegen
			</a><br/>
			{$addlabeldiv}
		{/if}
		{if $allowedit}
			{$editsubjectsourcediv}
		{/if}
		
		<h2>Gerelateerde bronnen</h2>
		{section name=i2 loop=$source->relatedSources}
			{if $allowedit}
				{$source->relatedSources[i2]->geteditbuttons()}
			{/if}
			<a href="index.php?actie=source&id={$source->relatedSources[i2]->referToObj->id}&comefrom={$comefrom}">{$source->relatedSources[i2]->referToObj->name}</a><br/>
		{/section}
		{if $allowadd}
			<a onclick="{$addsourceclick}">
				<img class="button" src="http://plaetjes.csrdelft.nl/documenten/plus.jpg"/>
				Bron-bron relatie toevoegen
			</a><br/>
			{$addsourcediv}
		{/if}
		{if $allowedit}
			{$editsourcesourcediv}
		{/if}
		
		<h2>Beoordelingen</h2>
		{section name=i3 loop=$source->opinions}
			{if $allowedit}
				{$source->opinions[i3]->geteditbuttons()}
			{/if}
			{$source->opinions[i3]->lid}: {$source->opinions[i3]->comment}<br/>
		{/section}
		{if $allowadd}
			<a href="index.php?actie=new&class=sourceopinion&sid={$source->id}">Beoordeling toevoegen</a><br/>
		{/if}
		
						
	</td></tr>
</table>
{if $source->sourceType=="discussion"}
	<!--TODO: uiteraard moet dit nog even fatsoenlijk gemaaktw orden -->
	<!--<iframe src="../forum/onderwerp/{$source->link}" width ="100%" height="800px"></iframe>-->
{/if}

 