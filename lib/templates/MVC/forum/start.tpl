{$view->getMelding()}

<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

<div class="forumNavigatie">
	<h1>Forum</h1>
</div>

<table id="forumtabel">
	{foreach from=$categorien item=cat}
		<thead>
			<tr>
				<th>{$cat->titel} <span class="forumcategorie-omschrijving">{$cat->omschrijving}</span></th>
				<th>Onderwerpen</th>
				<th>Berichten</th>
				<th>Recent</th>
			</tr>
		</thead>
		<tbody>
			{if !$cat->hasForumDelen()}
				<tr>
					<td colspan="4">Deze categorie is leeg.</td>
				</tr>
			{/if}
			{foreach from=$cat->getForumDelen() item=deel}
				{include file='MVC/forum/deel_lijst.tpl'}
			{/foreach}
		</tbody>
	{/foreach}
</table>