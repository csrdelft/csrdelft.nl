<div class="mededelingen-overzichtlijst">
	{if $geselecteerdeMededeling->magToevoegen()}<a class="knop" href="{$nieuws_root}toevoegen">{icon get="toevoegen"} Toevoegen</a>{/if}
	{if $geselecteerdeMededeling->isModerator()}
	<a class="knop" href="#" onclick="toggleDiv('legenda')">{icon get="legenda"} Legenda</a>
	<div id="legenda" style="display:none;">
		<span id="ubbsluiten" onclick="toggleDiv('legenda')" title="Legenda verbergen">&times;</span>
		<h2>Legenda Mededelingen</h2>
		<br />
		Voor de moderators zijn mededelingen in de lijst gemarkeerd. Dit is de betekenis van de markering:<br />
		<ul>
			<li><strong>dikgedrukte</strong> mededelingen wachten op goedkeuring</li>
			<li><em>schuingedrukte</em> mededelingen zijn zichtbaar voor iedereen (mits ze goedgekeurd zijn)</li>
			<li>normale tekst geeft aan dat mededelingen alleen zichtbaar zijn voor (oud)leden</li>
			<li><span style="color: grey;">grijs gekleurde</span> mededelingen zijn verborgen en dus alleen zichtbaar voor moderators</li>
		</ul>
		<br />
	</div>
	{/if}
	{if $geselecteerdeMededeling->magToevoegen() OR $geselecteerdeMededeling->isModerator()}
	<br />
	<br />	
	{/if}
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