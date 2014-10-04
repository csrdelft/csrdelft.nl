{if $geselecteerdeMededeling->isModerator()}
	<ul class="horizontal nobullets">
		<li{if !$prullenbak} class="active"{/if}>
			<a href="{$mededelingenketser_root}" title="Mededelingenketzer">Mededelingenketzer</a>
		</li>
		<li>
			<a href="{$pagina_root}top3overzicht/" title="Top 3 Overzicht">Top 3 Overzicht</a>
		</li>
		<li{if $prullenbak} class="active"{/if}>
			<a href={if !$prullenbak}"{$pagina_root}prullenbak/"{else}"{$pagina_root}"{/if} title="Prullenbak">Prullenbak</a>
		</li>
	</ul>
	<hr />
{/if}
<div id="mededelingenketser">
	{if $prullenbak}
		<h1>Mededelingen Prullenbak</h1>
		<div>Deze pagina bevat alleen verborgen, verwijderde en vervallen mededelingen.</div>
		<br />
	{/if}
	{getMelding()}
	{if $geselecteerdeMededeling!==null}		{*	Check of er een mededeling geselecteerd is.	Zo niet, dan
		is de database leeg en geven we een nette foutmelding.	*}
		<div id="kolomlinks">
			{* Knoppen bovenaan *}
			{if !$prullenbak AND $geselecteerdeMededeling->magToevoegen()}
				<a class="knop" href="{$pagina_root}toevoegen">{icon get="toevoegen"} Toevoegen</a>
			{/if}
			{if $geselecteerdeMededeling->isModerator()}
				<a class="knop" href="#" onclick="$('#legenda').toggle();">{icon get="legenda"} Legenda</a>
				<div id="legenda" class="dragobject verborgen">
					<span id="ubbsluiten" onclick="$('#legenda').toggle();" title="Legenda verbergen">&times;</span>
					<h2>Legenda Mededelingen</h2>
					<br />
					Voor de moderators zijn mededelingen in de lijst gemarkeerd. Dit is de betekenis van de markering:<br />
					<ul>
						<li><strong>dikgedrukte</strong> mededelingen wachten op goedkeuring</li>
						<li><em>schuingedrukte</em> mededelingen zijn zichtbaar voor iedereen (mits ze goedgekeurd zijn)</li>
						<li>normale tekst geeft aan dat mededelingen alleen zichtbaar zijn voor (oud)leden</li>
							{if $prullenbak}
							<li><span class="lichtgrijs">grijs gekleurde</span> mededelingen zijn verborgen en dus alleen zichtbaar in de prullenbak</li>
							<li><span class="error">rood gekleurde</span> mededelingen zijn verwijderd en dus alleen zichtbaar in de prullenbak</li>
							{/if}
					</ul>
					<br />
				</div>
			{/if}
			{if (!$prullenbak AND $geselecteerdeMededeling->magToevoegen()) OR $geselecteerdeMededeling->isModerator()}
				<br />
				<br />	
			{/if}

			{* Lijst met mededelingen *}
			{include file="mededelingen/lijst.tpl"}

			{* Lijst met (eigen) mededelingen die door de PubCie nog goedgekeurd moeten worden. *}
			{if !$prullenbak AND !empty($wachtGoedkeuring)}
				<div class="wachtgoedkeuring">
					<h2>Wachtend op goedkeuring van de PubCie:</h2><br />
					{foreach from=$wachtGoedkeuring key=groepering item=mededelingen}
						<div class="mededelingenlijst-block">
							<div class="mededelingenlijst-block-titel">{$groepering}</div>
							{foreach from=$mededelingen item=mededeling}
								<div {if $mededeling->getId()==$geselecteerdeMededeling->getId()}id="actief" {/if}class="mededelingenlijst-item{if $mededeling->isVerborgen()} verborgen-item{/if}">
									{if $mededeling->getCategorie()->getPlaatje() !=''}
										<div class="mededelingenlijst-plaatje">
											<a href="{$pagina_root}{$mededeling->getId()}">
												<img src="{$CSR_PICS}/nieuws/{$mededeling->getCategorie()->getPlaatje()}" width="10px" height="10px" />
											</a>
										</div>
									{/if}
									<div class="itemtitel">
										{* {$mededeling->getDatum()} *}
										<a href="{$pagina_root}{$mededeling->getId()}"{if $mededeling->isModerator()} class="{if !$mededeling->isPrive()}cursief{/if} {if $mededeling->getZichtbaarheid()=='wacht_goedkeuring'}dikgedrukt{/if}"{/if}>{$mededeling->getTitel()|ubb|html_substr:"40":"…"}</a>
									</div>
								</div>
							{/foreach}
						</div> {* Einde mededelingenlijst-block*}
					{/foreach}
				</div>
			{/if}
		</div> {* Einde kolom links *}

		<div id="kolomrechts">
			{* De mededeling rechtsbovenaan *}
			<div class="nieuwsbericht">
				<div class="nieuwsbody">
					<div class="nieuwstitel">{$geselecteerdeMededeling->getTitel()|escape:'html'}</div>
					<img class="nieuwsplaatje" src="{$CSR_PICS}/nieuws/{$geselecteerdeMededeling->getPlaatje()}" width="200px" height="200px" alt="{$geselecteerdeMededeling->getPlaatje()}" />
					{$geselecteerdeMededeling->getTekst()|ubb}<br />
				</div>
				<div class="informatie">
					<hr />
					Geplaatst op {$geselecteerdeMededeling->getDatum()|date_format:'%d-%m-%Y'}{if $geselecteerdeMededeling->isModerator()} door {$geselecteerdeMededeling->getUid()|csrnaam}{/if}<br />
					Categorie: {$geselecteerdeMededeling->getCategorie()->getNaam()}<br />
					{if $geselecteerdeMededeling->isModerator()}
						Doelgroep: {$geselecteerdeMededeling->getDoelgroep()}<br />
						Prioriteit: {$geselecteerdeMededeling->getPrioriteit()}<br />
					{/if}
					{if $geselecteerdeMededeling->isModerator() OR $geselecteerdeMededeling->magBewerken()}
						Vervalt {if $geselecteerdeMededeling->getVervaltijd()===null}nooit{else}op: {$geselecteerdeMededeling->getVervaltijd()|date_format:$datumtijdFormaat}{/if}<br />
					{/if}
					{if $geselecteerdeMededeling->isModerator() AND $geselecteerdeMededeling->isVerborgen()}
						Verborgen: ja<br />
					{/if}
					{if $geselecteerdeMededeling->magBewerken()}
						<a href="{$pagina_root}bewerken/{$geselecteerdeMededeling->getId()}">
							{icon get="bewerken"}
						</a>
						<a href="{$pagina_root}verwijderen/{$geselecteerdeMededeling->getId()}" onclick="return confirm('Weet u zeker dat u deze mededeling wilt verwijderen?');">
							{icon get="verwijderen"}
						</a>
						{if $geselecteerdeMededeling->isModerator() AND $geselecteerdeMededeling->getZichtbaarheid()=='wacht_goedkeuring'}
							<a onclick="return confirm('Weet u zeker dat u deze mededeling wilt goedkeuren?')" href="{$pagina_root}keur-goed/{$geselecteerdeMededeling->getId()}">
								{icon get="goedkeuren"}
							</a>
						{/if}
					{/if}
				</div>
			</div>

			{* Het Topmost block *}
			{if !$prullenbak}
				{'[mededelingen=top3leden]'|ubb}
			{/if}

		{else}		{* als $geselecteerdeMededeling===null *}
			Er zijn geen mededelingen gevonden...
		{/if}		{* Einde if $geselecteerdeMededeling!==null *}

		</div> {* Einde #mededelingenketser *}