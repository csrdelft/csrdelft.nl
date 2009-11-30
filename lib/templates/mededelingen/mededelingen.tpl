<div class="mededelingen-overzichtlijst">
{$melding}
{if $geselecteerdeMededeling!==null}		{*	Check of er een mededeling geselecteerd is.	Zo niet, dan
												is de database leeg en geven we een nette foutmelding.	*}
	{if $geselecteerdeMededeling->magToevoegen()}<a class="knop" href="{$nieuws_root}toevoegen">{icon get="toevoegen"} Toevoegen</a><br /><br />{/if}
	
	{foreach from=$lijst key=groepering item=mededelingen}
		<div class="mededelingenlijst-block">
			<div class="mededelingenlijst-block-titel">{$groepering}</div>
				{foreach from=$mededelingen item=mededeling}
					<div {if $mededeling->getId()==$geselecteerdeMededeling->getId()}id="actief" {/if}class="mededelingenlijst-item{if $mededeling->isVerborgen()} verborgen-item{/if}">
						{if $mededeling->getCategorie()->getPlaatje() !=''}
							<div class="mededelingenlijst-plaatje">
								<a href="{$nieuws_root}{$mededeling->getId()}">
									<img src="{$csr_pics}nieuws/{$mededeling->getCategorie()->getPlaatje()}" width="10px" height="10px" />
								</a>
							</div>
						{/if}
					<div class="itemtitel">
						{* {$mededeling->getDatum()} *}
						<a href="{$nieuws_root}{$mededeling->getId()}"{if $mededeling->isModerator()} style="{if !$mededeling->isPrive()}font-style: italic;{/if}{if $mededeling->getZichtbaarheid()=='wacht_goedkeuring'}font-weight: bold;{/if}"{/if}>{$mededeling->getAfgeknipteTitel()}</a>
					</div>
			</div>
				{/foreach}
		</div> {* Einde mededelingenlijst-block*}
	{/foreach}
		<div class="mededelingen_paginering">
		Pagina: {sliding_pager baseurl="`$nieuws_root`pagina/" 
					pagecount=$totaalAantalPaginas curpage=$huidigePagina
					txt_prev="&lt;" separator="" txt_next="&gt;" show_always=true show_first_last=false show_prev_next=false}
		</div>
	</div> {* Einde mededelingen-overzichtlijst *}
	
	<div style="width: 400px; float: left;">
		<div class="nieuwsbericht">
			<div class="nieuwsbody">
				<div class="nieuwstitel">{$geselecteerdeMededeling->getTitel()|escape:'html'}</div>
				{if $geselecteerdeMededeling->isVerborgen()}
					<em>[verborgen]</em><br />
				{/if}
				<img class="nieuwsplaatje" src="{$csr_pics}nieuws/{$geselecteerdeMededeling->getPlaatje()}" width="200px" height="200px" alt="{$geselecteerdeMededeling->getPlaatje()}" />
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
				{if $geselecteerdeMededeling->magBewerken()}
					<a href="{$nieuws_root}bewerken/{$geselecteerdeMededeling->getId()}">
						{icon get="bewerken"}
					</a>
					<a href="{$nieuws_root}verwijderen/{$geselecteerdeMededeling->getId()}" onclick="return confirm('Weet u zeker dat u deze mededeling wilt verwijderen?');">
						{icon get="verwijderen"}
					</a>
					{if $geselecteerdeMededeling->isModerator() AND $geselecteerdeMededeling->getZichtbaarheid()=='wacht_goedkeuring'}
						<a onclick="return confirm('Weet u zeker dat u deze mededeling wilt goedkeuren?')" href="{$nieuws_root}keur-goed/{$geselecteerdeMededeling->getId()}">
							{icon get="goedkeuren"}
						</a>
					{/if}
				{/if}
			</div>
		</div>
		
		{* Het Topmost block *}
		{'[mededelingen=top3leden]'|ubb}
	{else}
		Er zijn geen mededelingen gevonden...
	{/if}
</div>
