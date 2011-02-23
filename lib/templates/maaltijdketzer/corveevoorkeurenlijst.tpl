{assign var='actief' value='corveevoorkeuren'}
{include file='maaltijdketzer\menu.tpl'}

<h1>Corveevoorkeuren</h1>

<table class="maaltijden">
	{section name=leden loop=$leden}
		{assign var='index' value=$smarty.section.leden.index}
		{if $index%30 == 0}
			<tr>				
                <th>&nbsp;</th>
				<th><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/uid/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Naam</a></th>
                {section name=header loop=$voorkeurenheaders}                    
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_{$smarty.section.header.index}/{if $sorteer_richting=='asc'}desc{else}asc{/if}">{$voorkeurenheaders[header]}</a></th>    
                {/section}                
				<th><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/corvee_vrijstelling/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Vrijstelling</a></th>
				<th><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/corvee_punten/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Punten</a></th>
                <th><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/corvee_prognose/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Prognose</a></th>
			</tr>
		{/if}
		{assign var='lid' value=$leden.$index.uid}
		{if $lid!='' and $loginlid->hasPermission('P_MAAL_MOD')}				
            <tr style="background-color: {cycle values='#e9e9e9, #fff'};{if $bewerkt_lid==$lid}background-color: #bfb{else}{/if}">
                <td><a name="lid_{$lid}"></a></td>
                <td>{$lid|csrnaam}</td>                    
                {assign var='voorkeuren' value=$leden.$index.corvee_voorkeuren}
                {section name=voorkeuren loop=$voorkeuren}
                    {assign var='it_voorkeuren' value=$smarty.section.voorkeuren.iteration-1}
                        <td>{if $voorkeuren.$it_voorkeuren}{$voorkeuren.$it_voorkeuren}{/if}</td>
                {/section}
                <td>{$leden.$index.corvee_vrijstelling}%</td>
                <td>{$leden.$index.corvee_punten}</td>								
                <td>{$leden.$index.corvee_prognose}</td>								
            </tr>
		{else}
        {/if}
	{/section}
</table>
<br />