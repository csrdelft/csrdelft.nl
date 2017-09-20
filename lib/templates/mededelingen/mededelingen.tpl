{if $model->isModerator()}
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
	{getMelding}
	{if !empty($geselecteerdeMededeling)}		{*	Check of er een mededeling geselecteerd is.	Zo niet, dan
		is de database leeg en geven we een nette foutmelding.	*}
		<div id="kolomlinks">
			{* Knoppen bovenaan *}
			{if !$prullenbak AND $model->magToevoegen()}
				<a class="btn" href="{$pagina_root}toevoegen">{icon get="toevoegen"} Toevoegen</a>
			{/if}
			{if $model->isModerator()}
				<a class="btn" href="#" onclick="$('#legenda').toggle();">{icon get="legenda"} Legenda</a>
				<div id="legenda" class="dragobject verborgen">
					<div class="float-right" onclick="$('#legenda').fadeOut();" title="Legenda verbergen">&times;</div>
					<h3>Legenda Mededelingen</h3>
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
			{if (!$prullenbak AND $model->magToevoegen()) OR $model->isModerator()}
				<br />
				<br />
			{/if}

			{* Lijst met mededelingen *}
			{include file="mededelingen/lijst.tpl"}

			{* Lijst met (eigen) mededelingen die door de PubCie nog goedgekeurd moeten worden. *}
			{if !$prullenbak AND !empty($wachtGoedkeuring)}
				<div class="wachtgoedkeuring">
					<h3>Wachtend op goedkeuring van de PubCie:</h3><br />
					{foreach from=$wachtGoedkeuring key=groepering item=mededelingen}
						<div class="mededelingenlijst-block">
							<div class="mededelingenlijst-block-titel">{$groepering}</div>
							{foreach from=$mededelingen item=mededeling}
								<div {if $mededeling->id==$geselecteerdeMededeling->id}id="actief" {/if}class="mededelingenlijst-item{if $mededeling->verborgen} verborgen-item{/if}">
									{if $mededeling->getCategorie()->plaatje !=''}
										<div class="mededelingenlijst-plaatje">
											<a href="{$pagina_root}{$mededeling->id}">
												<img src="/plaetjes/nieuws/{$mededeling->getCategorie()->plaatje}" width="10px" height="10px" />
											</a>
										</div>
									{/if}
									<div class="itemtitel">
										{* {$mededeling->getDatum()} *}
										<a href="{$pagina_root}{$mededeling->id}"{if $model->isModerator()} class="{if !$mededeling->prive}cursief{/if} {if $mededeling->zichtbaarheid=='wacht_goedkeuring'}dikgedrukt{/if}"{/if}>{$mededeling->titel|bbcode|html_substr:"40":"…"}</a>
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
					<div class="nieuwstitel">{$geselecteerdeMededeling->titel|escape:'html'}</div>
					<img class="nieuwsplaatje" src="/plaetjes/mededelingen/{$geselecteerdeMededeling->plaatje}" width="200px" height="200px" alt="{$geselecteerdeMededeling->plaatje}" />
					{$geselecteerdeMededeling->tekst|bbcode}<br />
				</div>
				<div class="informatie">
					<hr />
					Geplaatst op {$geselecteerdeMededeling->datum|date_format:'%d-%m-%Y'}{if $model->isModerator()} door {$geselecteerdeMededeling->getProfiel()->getLink('civitas')}{/if}<br />
					Categorie: {$geselecteerdeMededeling->getCategorie()->naam}<br />
					{if $model->isModerator()}
						Doelgroep: {$geselecteerdeMededeling->doelgroep}<br />
						Prioriteit: {$geselecteerdeMededeling->prioriteit}<br />
					{/if}
					{if $geselecteerdeMededeling->magBewerken()}
						Vervalt {if $geselecteerdeMededeling->vervaltijd===null}nooit{else}op: {$geselecteerdeMededeling->vervaltijd|date_format:$datumtijdFormaat}{/if}<br />
					{/if}
					{if $model->isModerator() AND $geselecteerdeMededeling->verborgen}
						Verborgen: ja<br />
					{/if}
					{if $geselecteerdeMededeling->magBewerken()}
						<a href="{$pagina_root}bewerken/{$geselecteerdeMededeling->id}">
							{icon get="bewerken"}
						</a>
						<a href="{$pagina_root}verwijderen/{$geselecteerdeMededeling->id}" onclick="return confirm('Weet u zeker dat u deze mededeling wilt verwijderen?');">
							{icon get="verwijderen"}
						</a>
						{if $model->isModerator() AND $geselecteerdeMededeling->zichtbaarheid=='wacht_goedkeuring'}
							<a onclick="return confirm('Weet u zeker dat u deze mededeling wilt goedkeuren?')" href="{$pagina_root}goedkeuren/{$geselecteerdeMededeling->id}">
								{icon get="goedkeuren"}
							</a>
						{/if}
					{/if}
				</div>
			</div>

			{* Het Topmost block *}
			{if !$prullenbak}
				{'[mededelingen=top3leden]'|bbcode}
			{/if}

		{else}		{* als $geselecteerdeMededeling===null *}
			Er zijn geen mededelingen gevonden...
		{/if}		{* Einde if $geselecteerdeMededeling!==null *}

		</div> {* Einde #mededelingenketser *}
