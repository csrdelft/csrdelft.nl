<ul class="horizontal nobullets">
	<li>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li>
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
	{if $loginlid->hasPermission('P_MAAL_MOD')}
		<li>
			<a href="/actueel/maaltijden/beheer/" title="Beheer">Maaltijdbeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/corveebeheer/" title="Corveebeheer">Corveebeheer</a>
		</li>
		<li class="active">
			<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
		</li>
	{/if}
</ul>
<hr />
<h1>Corveepunten</h1>

<table class="maaltijden">
	<tr>
		<th>&nbsp;</th>
		<th><a href="/actueel/maaltijden/corveepunten/sorteer/uid/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Naam</a></th>
		<!--<th>Voorkeuren</th>-->
		<th style="width: 20px"><a href="/actueel/maaltijden/corveepunten/sorteer/kok/{if $sorteer_richting=='asc'}desc{else}asc{/if}">K</a></th>
		<th style="width: 20px"><a href="/actueel/maaltijden/corveepunten/sorteer/afwas/{if $sorteer_richting=='asc'}desc{else}asc{/if}">A</a></th>
		<th style="width: 40px"><a href="/actueel/maaltijden/corveepunten/sorteer/theedoek/{if $sorteer_richting=='asc'}desc{else}asc{/if}">T</a></th>
		<th style="width: 70px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_kwalikok/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Kwalikok</a></th>
		<th style="width: 70px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_punten/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Punten</a></th>
		<!--<th>Ingeroosterd</th>-->
		<th style="width: 90px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_vrijstelling/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Vrijstelling</a></th>
		<th style="width: 50px"><a href="/actueel/maaltijden/corveepunten/sorteer/corvee_tekort/{if $sorteer_richting=='asc'}desc{else}asc{/if}">Tekort</a></th>
		<th style="width: 20px">&nbsp;</th>
	</tr>
	{section name=leden loop=$leden}					
		{assign var='it' value=$smarty.section.leden.iteration-1}
		{assign var='lid' value=$leden.$it.uid}
		{if $lid!=''}
			<form id="{$lid}" action="/actueel/maaltijden/corveepunten/" method="post">
			<input type="hidden" name="uid" value="{$lid}" />
			<input type="hidden" name="sorteer" value="{$sorteer}" />
			<input type="hidden" name="sorteer_richting" value="{$sorteer_richting}" />
			<input type="hidden" name="actie" value="bewerk" />

			<tr {if $bewerkt_lid==$lid}style="background-color: #bbffbb"{/if}>
				<td></td>
				<td>{$lid|csrnaam}</td>
				<!--<td>{$leden.$it.corvee_voorkeuren}</td>-->
				<td>{$leden.$it.kok}</td>
				<td>{$leden.$it.afwas}</td>
				<td>{$leden.$it.theedoek}</td>
				<td><input type="checkbox" name="corvee_kwalikok" value="1" {if $leden.$it.corvee_kwalikok}checked="checked"{/if} /></td>
				<td><input type="text" name="corvee_punten" value="{$leden.$it.corvee_punten}" style="width: 50px;" /></td>
				<td><input type="text" name="corvee_vrijstelling" value="{$leden.$it.corvee_vrijstelling}" style="width: 50px;" /></td>
				<td>{$leden.$it.corvee_tekort}</td>
				<td><input type="submit" name="submit" value="OK" /></td>
			</tr>
			
			</form>
		{else}FOUT{/if}
	{/section}
</table>
<br />