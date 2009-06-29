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
		<th>Naam</th>					
		<th>K</th>
		<th>A</th>
		<th>T</th>
		<th>Punten</th>
		<!--<th>Ingeroosterd</th>-->
		<th>Vrijstelling</th>
		<th>Tekort</th>
	</tr>
	{section name=leden loop=$leden}					
		{assign var='it' value=$smarty.section.leden.iteration-1}
		{assign var='lid' value=$leden.$it.uid}
		{if $lid!=''}
			<tr>
				<td></td>
				<td>{$lid|csrnaam}</td>
				<td>{$leden.$it.kok}</td>
				<td>{$leden.$it.afwas}</td>
				<td>{$leden.$it.theedoek}</td>
				<td>{$leden.$it.corvee_punten}</td>
				<td>{$leden.$it.corvee_vrijstelling}</td>
				<td>{$leden.$it.corvee_tekort}</td>
			</tr>
		{else}FOUT{/if}
	{/section}
</table>
<br />