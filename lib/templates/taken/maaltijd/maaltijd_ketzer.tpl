{*
	maaltijd_ketzer.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div class="ubb_block ubb_maaltijd" id="maaltijdketzer-{$maaltijd->getMaaltijdId()}">
{if $loginlid->hasPermission('P_LOGGED_IN')}
	<div class="aanmelddata">U komt:<br />
	{if $aanmelding}
		{if $aanmelding->getDoorAbonnement()}
			<em>eten (abo)</em>
		{else}
			<em>eten</em>
		{/if}
	{else}
		<em>niet eten</em>
	{/if}
		<br />
	{if $maaltijd->getIsGesloten()}
		Gesloten
	{else}
		{if $loginlid->hasPermission('P_MAAL_IK')}
			{if $aanmelding}
				<a href="javascript:void(0);" onclick="ketzer_post('/actueel/taken/maaltijden/afmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');"><strong>af</strong>melden</a>
			{elseif $maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()}
				Vol
			{else}
				<a href="javascript:void(0);" onclick="ketzer_post('/actueel/taken/maaltijden/aanmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');"><strong>aan</strong>melden</a>
			{/if}
		{/if}
	{/if}
	</div>
{/if}
<div class="maaltijdgegevens">
	<h2>Maaltijd van {$maaltijd->getDatum()|date_format:"%A %e %b"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h2>
	{$maaltijd->getTitel()}<br />
	<span class="small">
{if $toonlijst}
		<a href="/actueel/taken/maaltijdenbeheer/lijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst">
{/if}
			Inschrijvingen: <em>{$maaltijd->getAantalAanmeldingen()}</em> van <em>{$maaltijd->getAanmeldLimiet()}</em>
{if $toonlijst}
		</a>
{/if}
	</span>
</div>
</div>