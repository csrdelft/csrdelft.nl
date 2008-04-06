<h1>Bron {$source->name} ({$source->sourceType})</h1>
<table width = "100%">
	<tr><td width= "60%">
		<h2>Bron</h2>
		{if $source->sourceType=="link"}
			<a href="{$source->link}" target="_blank">{$source->link}</a>
		{/if}
		{if $source->sourceType=="discussion"}
			<a href="../forum/onderwerp/{$source->link}">{$source->name}</a>
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
			<a onclick="{$addlabelclick}">Label toevoegen</a><br/>
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
			<a href="index.php?actie=source&id={$source->relatedSources[i2]->referToObj->id}">{$source->relatedSources[i2]->referToObj->name}</a><br/>
		{/section}
		{if $allowadd}
			<a onclick="{$addsourceclick}">Bron-bron relatie toevoegen</a><br/>
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

 