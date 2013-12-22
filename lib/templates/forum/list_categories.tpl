<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></fieldset></form>

<div class="forumNavigatie">
	<h1 style="width: 200px;">Forum</h1>
</div>
{$melding}

<table id="forumtabel">
	<tr>
		<th>Forum</th>
		<th>Onderwerpen</th>
		<th>Berichten</th>
		<th>Verandering</th>
	</tr>
	{foreach from=$categories item='categorie'}
		{if $categorie.titel=='SEPARATOR'}
			<tr class="tussenschot"><td colspan="4"></td></tr>
		{else}
			<tr class="kleur{cycle values="0,1"}">
				<td class="titel">
					<a href="/communicatie/forum/categorie/{$categorie.id}">{$categorie.titel|escape:'html'}</a><br />
					{$categorie.beschrijving|escape:'html'}
				</td>
				<td class="reacties">{$categorie.topics}</td>
				<td class="reacties">{$categorie.reacties}</td>
				<td class="reactiemoment">
					{if $categorie.lastpost=='0000-00-00 00:00:00'}
						nog geen berichten
					{else} 
						{$categorie.lastpost|reldate}<br />
						<a href="/communicatie/forum/reactie/{$categorie.lastpostID}">bericht</a> 
						{if $categorie.lastuser!=''}
							door {$categorie.lastuser|csrnaam:'user'}
						{/if}
					{/if}
				</td>
			</tr>
		{/if}
	{foreachelse}
		{* het forum is nog leeg, of de database is stuk ofzo *}
		<tr>
			<td colspan="4">
				Er zijn nog geen categorie&euml;n of er is iets mis met het databeest
			</td>
		</tr> 
	{/foreach}
	<tr>
		<th>Forum</th>
		<th>onderwerpen</th>
		<th>berichten</th>
		<th>verandering</th>
	</tr>
</table>