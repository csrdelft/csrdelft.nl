
<table width = "100%" cellspacing="10px">
	<tr><td colspan="2"><h1>Bron: {$source->name}</h1></td></tr>
	<tr><td width= "60%">
		<!--
		{if $comefrom != "-1"}
			<a href="index.php?actie=subject&id={$comefrom}">&lt;&lt;terug naar onderwerp</a>
		{/if}
		-->
		<h2><img class="plaatje" src="{$source->getImage()}"/> Details</h2>
		{if $source->sourceType=="link"}
			<b>Link: </b><a href="{$source->link}" target="_blank">{$source->link}</a>
		{/if}
		{if $source->sourceType=="discussion"}
			<b>Link: </b><a href="../forum/onderwerp/{$source->link}">{$source->name}</a>
		{/if}
		{if $source->sourceType=="file"}
			<b>Link: </b><a href="../communicatie/documenten/neerladen/{$source->link}" target="_blank">{$source->name} downloaden</a>
		{/if}
		{if $source->sourceType=="book"}
			<b>Schrijver:</b> {$source->schrijver}<br/>
			<b>Uitgever:</b> {$source->uitgever}<br/>
			<b>Jaar:</b> {$source->jaar}<br/>
			<b>Isbn:</b> {$source->isbn}
		{/if}
		<br/><br/>
		Gepost door {$source->lid} op {$source->createdate}<br/>
		Beoordeling {$source->voting()} ({$source->votecount} stemmen totaal)
		<br/><br/><h2>Omschrijving</h2>
		{$source->description|ubb}

	</td><td><div class="thema-grotebalk">
		<h2>Koppelingen</h2>
		<h3>Onderwerpen</h3><br/>
		{section name=i1 loop=$source->parents}
			<div class="thema-grotebalk">
				<img class="plaatje" src="images/leaf.png"/>
				<div class="bericht">
					{if $allowedit}
						{$source->parents[i1]->geteditbuttons()}
					{/if}
					<a href="index.php?actie=subject&id={$source->parents[i1]->subjid}">{$source->parents[i1]->subjname}</a>
				</div>
			</div>
		{/section}
		{if $allowadd}
			<div class="thema-grotebalk">
				<a onclick="{$addlabelclick}">
					<img class="button" src="images/add.png"/>
					Toevoegen
				</a>
			</div>
			{$addlabeldiv}
		{/if}
		{if $allowedit}
			{$editsubjectsourcediv}
		{/if}
		
		<h3>Gerelateerde bronnen</h3><br/>
		{section name=i2 loop=$source->relatedSources}
			<div class="thema-grotebalk">
				<img class="plaatje" src="{$source->relatedSources[i2]->getImage()}"/>
				<div id="bericht">
					{if $allowedit}
						{$source->relatedSources[i2]->geteditbuttons()}
					{/if}
					<a href="index.php?actie=source&id={$source->relatedSources[i2]->referToObj->id}&comefrom={$comefrom}">
						{$source->relatedSources[i2]->referToObj->name}
					</a>
				</div>
			</div>
		{/section}
		{if $allowadd}
			<div class="thema-grotebalk">
				<a onclick="{$addsourceclick}">
					<img class="button" src="images/add.png"/>
					Toevoegen
				</a>
			</div>
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
			{$source->opinions[i3]->lid}: {$source->opinions[i3]->comment|ubb}<br/>
		{/section}
		{if $allowadd}
			<div class="thema-grotebalk">
				<a href="index.php?actie=new&class=sourceopinion&sid={$source->id}"><img class="button" src="images/add.png"/> 
					Toevoegen
				</a>
			</div>
		{/if}
	</div></td></tr>
</table>
 
