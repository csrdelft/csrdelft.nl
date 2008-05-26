<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a>
		<h1>Recente forumberichten</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

<table id="forumtabel">
	<tr>
		<th>Titel</th>
		<th>Reacties</th>
		<th>verandering</th>
	</tr>
	{foreach from=$berichten item=bericht}
		<tr class="kleur{cycle values="0,1"}">
			<td class="titel">
				{if $bericht.soort=='T_POLL'}[peiling]{/if}
				{if $bericht.zichtbaar=='wacht_goedkeuring'}[ter goedkeuring...]{/if}
				<a href="/communicatie/forum/onderwerp/{$bericht.id}">
					{if $bericht.plakkerig==1}
						<img src="{$csr_pics}forum/plakkerig.gif" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;
					{/if}	
					{if $bericht.open==0}
						<img src="{$csr_pics}forum/slotje.png" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;
					{/if}
					{$bericht.titel|wordwrap:60:"\n":true|escape:'html'}
				</a>
			</td>
			<td class="reacties">{$bericht.reacties-1}</td>
			<td class="reactiemoment">
				{$bericht.lastpost|reldate}<br />
				<a href="/communicatie/forum/onderwerp/{$bericht.id}#post{$bericht.lastpostID}">bericht</a> door 
				{$bericht.uid|csrnaam}
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="3">Deze categorie bevat nog geen berichten of deze categorie bestaat niet.</td>
		</tr>
	{/foreach}
	<tr>
		<th>Titel</th>
		<th>Reacties</th>
		<th>verandering</th>
	</tr>
</table>
{$smarty.capture.navlinks}