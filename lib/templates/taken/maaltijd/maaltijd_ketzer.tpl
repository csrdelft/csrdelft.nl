{*
	maaltijd_ketzer.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<div class="ubb_block ubb_maaltijd" id="maaltijdketzer-{$maaltijd->getMaaltijdId()}">{strip}
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
				<a onclick="ketzer_ajax('/maaltijdenketzer/afmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');"><strong>af</strong>melden</a>
			{elseif $maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()}
				Vol
			{else}
				<a onclick="ketzer_ajax('/maaltijdenketzer/aanmelden/{$maaltijd->getMaaltijdId()}', '#maaltijdketzer-{$maaltijd->getMaaltijdId()}');"><strong>aan</strong>melden</a>
			{/if}
		{/if}
	{/if}
	</div>
{/if}
<div class="maaltijdgegevens">
	<h2>Maaltijd van {$maaltijd->getDatum()|date_format:"%A %e %b"} {$maaltijd->getTijd()|date_format:"%H:%M"}</h2>
	{$maaltijd->getTitel()}
{if $toonlijst|is_a:'\Taken\CRV\CorveeTaak'}
	<div style="float: right; margin: 15px 10px 0px 0px;">
		{icon get="paintcan" title=$toonlijst->getCorveeFunctie()->getNaam()}
	</div>
{/if}
	<div class="small">
{if $toonlijst}
		<a href="/maaltijdenlijst/{$maaltijd->getMaaltijdId()}" title="Toon maaltijdlijst">
{/if}
			Inschrijvingen: <em>{$maaltijd->getAantalAanmeldingen()}</em> van <em>{$maaltijd->getAanmeldLimiet()}</em>
{if $toonlijst}
		</a>
{/if}
	</div>
</div>
</div>{/strip}