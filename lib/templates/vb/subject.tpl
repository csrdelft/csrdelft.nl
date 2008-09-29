<table width = "100%" cellspacing="10px">
	<tr><td colspan="2"><h1>{$sub->name}</h1>
				{if $sub->parent != "-1"}
				<a href="index.php?actie=subject&id={$sub->parent}">[omhoog]</a>
			{/if}
	</td></tr>
	<tr>
		<td width="50%">
			<h2>Omschrijving</h2>
			<!-- this field is required for the addsource forms, so a sourcelink can be created automatically to this subject -->
			<input type="hidden" id="SubjectIdField" value="{$sub->id}"/>
			{$sub->description|ubb}
			{* create a button to change a subject type *}
			{if $allowedit}
				<br/>
				{if $sub->isLeaf==true}
				<a href="#" onclick=
				"if(confirm('Weet u zeker dat u dit onderwerp naar een knoop wilt converteren? Voor het converteren naar knoop worden de bronnen in de tijdelijke map \'diversen\' geplaatst.  Knopen kunnen alleen subonderwerpen bevatten.'))
					document.location = 'index.php?actie=convertsubject&id={$sub->id}&target=knoop'"
					title="Converteer dit onderwerp naar 'subonderwerpen' (knoop) onderwerp">Converteer...</a>
				{else}
				<a href="#" onclick=
				"if(confirm('Weet u zeker dat u dit onderwerp naar een blad wilt converteren? Knoop onderwerpen kunnen alleen geconverteerd worden als alle kinderen bladeren zijn. Baderen kunnen alleen bronnen bevatten.'))
					document.location = 'index.php?actie=convertsubject&id={$sub->id}&target=blad'"
					title="Converteer dit onderwerp naar 'subbronnen' (blad) onderwerp">Converteer...</a>
				</br>
				{/if}
			{/if}
			{if $sub->isLeaf==false}
				<br/><h2>Subonderwerpen</h2><br/>
				{section name=sec2 loop=$sub->children}
					<div class="thema-grotebalk">
						<table>
							<tr><td>
								<img class="plaatje" src="{$sub->children[sec2]->getImage()}"/>
								{if $allowedit}
									{$sub->children[sec2]->geteditbuttons()}
								{/if}
							</td><td>
								<div class="titel">
									<a href="index.php?actie=subject&id={$sub->children[sec2]->id}">
										{$sub->children[sec2]->name}
									</a>
								</div>
								<div class="bericht">
									{$sub->children[sec2]->description|ubb}
								</div>
							</td></tr>
						</table>							
					</div>
				{/section}
				{if $allowadd}
					<a href="#" onclick="{$addsubjectclick}">
						<img class="button" src="images/add.png"/>
						Voeg sub onderwerp toe
					</a>
					{$editdiv}										
				{/if}
			{else}
				<h2>Forum discussies</h2><br/>
				{section name=sec2 loop=$sub->discussions}
					{* because a source has multiple parents, provided the parent where we came from, to be able to 
					produce the bar in future *}
					<div class="thema-grotebalk">
						<img class="plaatje" src="{$sub->discussions[sec2]->getImage()}"/>
						<div class="bericht">
							{if $allowedit}
								{$sub->discussions[sec2]->geteditbuttons()}
							{/if}
							<a href="index.php?actie=source&id={$sub->discussions[sec2]->id}&comefrom={$sub->id}">
								{$sub->discussions[sec2]->name}
							</a>
						</div>
					</div>
				{/section}
				{if $allowadd}
					<a href="#" onClick="
 						document.getElementById('sourceTypeDropDown').value='discussion';
						document.getElementById('goAddSource').click();
					">
					<img class="button" src="images/add.png"/>
						Voeg discussie toe
					</a>
				{/if}
			{/if}
		</td>
		{if $sub->isLeaf}
			<td>
				<h2>Bronnen</h2><br/>
				{section name=sec1 loop=$sub->sources}
					{* because a source has multiple parents, provided the parent where we came from, to be able to 
					produce the bar in future *}
					<div class="thema-grotebalk">
						<img class="plaatje" src="{$sub->sources[sec1]->getImage()}"/>
						<div class="bericht">
							{if $allowedit}
								{$sub->sources[sec1]->geteditbuttons()}
							{/if}
							<a href="index.php?actie=source&id={$sub->sources[sec1]->id}&comefrom={$sub->id}">
								{$sub->sources[sec1]->name}
							</a>
						</div>
					</div>
				{/section}
				{if $allowadd}
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
			
