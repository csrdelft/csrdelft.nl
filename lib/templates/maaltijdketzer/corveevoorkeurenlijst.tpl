<ul class="horizontal nobullets">
	<li>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li>
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
	<li>
		<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
	</li>
	{if $loginlid->hasPermission('P_MAAL_MOD')}
		<li>
			<a href="/actueel/maaltijden/corveebeheer/" title="Corveebeheer">Corveebeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/beheer/" title="Beheer">Maaltijdbeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
		</li>
	{/if}
</ul>
<hr />
<h1>Corveevoorkeuren</h1>

<table class="maaltijden">
	{section name=leden loop=$leden}
		{assign var='it' value=$smarty.section.leden.iteration-1}
		{if $it%30 == 0}
			<tr>
				<th>&nbsp;</th>
				<th><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/uid/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Naam</a></th>
				<th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_0/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Ma Kok</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_1/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Ma Afw</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_2/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Do Kok</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_3/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Do Afw</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_4/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Theedk</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_5/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Sc Fri</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_6/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Sc Afz</a></th>
                <th style="width: 15px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/voorkeur_7/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Sc Keu</a></th>
                
				<th style="width: 75px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/corvee_vrijstelling/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Vrijstelling</a></th>
				<th style="width: 60px"><a href="/actueel/maaltijden/corveevoorkeurenlijst/sorteer/corvee_punten/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Punten</a></th>
			</tr>
		{/if}
		{assign var='lid' value=$leden.$it.uid}
		{if $lid!=''}
			{if $loginlid->hasPermission('P_MAAL_MOD')}				
				<tr style="background-color: {cycle values="#e9e9e9, #fff"};{if $bewerkt_lid==$lid}background-color: #bfb{else}{/if}">
					<td><a name="lid_{$lid}"></a></td>
					<td>{$lid|csrnaam}</td>                    
                    {assign var='voorkeuren' value=$leden.$it.corvee_voorkeuren}
                    {section name=voorkeuren loop=$voorkeuren}
                        {assign var='it_voorkeuren' value=$smarty.section.voorkeuren.iteration-1}
                            <td>{if $voorkeuren.$it_voorkeuren}{$voorkeuren.$it_voorkeuren}{/if}</td>
					{/section}
                    <td>{$leden.$it.corvee_vrijstelling}%</td>
					<td>{$leden.$it.corvee_punten}</td>								
				</tr>
				
				</form>
			{else}				
			{/if}
		{else}FOUT{/if}
	{/section}
</table>
<br />