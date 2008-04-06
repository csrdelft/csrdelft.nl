<h1>Thema {$sub->name}</h1>
{if $sub->parent != "0"}
	Terug omhoog naar <a href="index.php?actie=subject&id={$sub->parent}">{$sub->parentobj->name}</a>
{/if}
<table width="100%">
	<tr>
		<td width="60%">
			<h2>Omschrijving</h2>
			<!-- this field is required for the addsource forms, so a sourcelink can be created automatically to this subject -->
			<input type="hidden" id="SubjectIdField" value="{$sub->id}"/>
			{$sub->description}
			{* create a button to change a subject type *}
			{if $allowedit}
				<br/>
				{if $sub->isLeaf==true}
				<a href="#" onclick=
				"if(confirm('Weet u zeker dat u dit onderwerp naar een knoop wilt converteren? Voor het converteren naar knoop worden de bronnen in de tijdelijke map \'diversen\' geplaatst.  Knopen kunnen alleen subonderwerpen bevatten.'))
					document.location = 'index.php?actie=convertsubject&id={$sub->id}&target=knoop'">Converteer dit onderwerp naar 'subonderwerpen' (knoop) onderwerp</a>
				{else}
				<a href="#" onclick=
				"if(confirm('Weet u zeker dat u dit onderwerp naar een blad wilt converteren? Knoop onderwerpen kunnen alleen geconverteerd worden als alle kinderen bladeren zijn. Baderen kunnen alleen bronnen bevatten.'))
					document.location = 'index.php?actie=convertsubject&id={$sub->id}&target=blad'">Converteer dit onderwerp naar 'subbronnen' (blad) onderwerp</a>
				{/if}
			{/if}
			{if $sub->isLeaf==false}
				<h2>Subonderwerpen</h2>
				{section name=sec2 loop=$sub->children}
					<br/>
					{if $allowedit}
						{$sub->children[sec2]->geteditbuttons()}
					{/if}
					<a href="index.php?actie=subject&id={$sub->children[sec2]->id}">
						{$sub->children[sec2]->name}
					</a>
				{/section}
				{if $allowadd}
					<br/>
					<a href="#" onclick="{$addsubjectclick}">Voeg sub onderwerp toe</a>
					{$editdiv}										
				{/if}
			{else}
				<h2>Forum onderwerpen</h2>
				{section name=sec2 loop=$sub->discussions}
					{* because a source has multiple parents, provided the parent where we came from, to be able to 
					produce the bar in future *}
					<br/>
					{if $allowedit}
						{$sub->discussions[sec2]->geteditbuttons()}
					{/if}
					<a href="index.php?actie=source&id={$sub->discussions[sec2]->id}&comefrom={$sub->id}">
						{$sub->discussions[sec2]->name}
					</a>
				{/section}
				{if $allowadd}
					<br/><a href="#" onClick="
 						document.getElementById('sourceTypeDropDown').value='discussion';
						document.getElementById('goAddSource').click();
					">Voeg discussie toe</a>
				{/if}
			{/if}
		</td>
		{if $sub->isLeaf}
			<td>
				<h2>Bronnen</h2>
				{section name=sec1 loop=$sub->sources}
					{* because a source has multiple parents, provided the parent where we came from, to be able to 
					produce the bar in future *}
					<br/>
					{if $allowedit}
						{$sub->sources[sec1]->geteditbuttons()}
					{/if}
					<a href="index.php?actie=source&id={$sub->sources[sec1]->id}&comefrom={$sub->id}">
						{$sub->sources[sec1]->name}
					</a>
				{/section}
				{if $allowadd}
					<br/>
					Voeg bron toe:
					<select id="sourceTypeDropDown">
						<option value="link" selected="selected">Link</option>
						<option value="file"/>Bestand</option>
						<option value="discussion"/>Discussie</option>
						<option value="book"/>Boek</option>
					</select> 
					<input id ="goAddSource" type="button" value="Ga" onclick="{$addsourceclick}">
					{$editlinkdiv}
					{$editfilediv}
					{$editdiscussiondiv}
					{$editbookdiv}
				{/if}
			</td>
		{/if}
	</tr>
</table>
			
