<div class="mededelingen-overzichtlijst">
{$melding}
{if $selectedMededeling->magToevoegen()}<a class="knop" href="{$nieuws_root}toevoegen">{icon get="toevoegen"} Toevoegen</a><br /><br />{/if}
{if empty($lijst)}
	Zoals het is, zoals het was, o Civitas!<br />(Geen mededelingen gevonden dus…)<br /><br />
{else}
	{foreach from=$lijst key=groepering item=mededelingen}
	<div class="mededelingenlijst-block">
		<div class="mededelingenlijst-block-titel">{$groepering}</div>
		{foreach from=$mededelingen item=mededeling}
			<div {if $mededeling->getId()==$selectedMededeling->getId()}id="actief" {/if}class="mededelingenlijst-item{if $mededeling->isVerborgen()} verborgen-item{/if}">
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
	</div>
	{/foreach}
	<div class="mededelingen_paginering">
	Pagina: {sliding_pager baseurl="`$nieuws_root`pagina/" 
				pagecount=$totaalAantalPaginas curpage=$huidigePagina
				txt_prev="&lt;" separator="" txt_next="&gt;" show_always=true show_first_last=false show_prev_next=false}
	</div>
{/if}
</div>

<div style="width: 400px; float: left;">
	
	{if $selectedMededeling!=null}		{*	If there is no Mededeling selected, there is something
											wrong, but we don't wat the user to know. *}
		<div class="nieuwsbericht">
			<div class="nieuwsbody">
				<div class="nieuwstitel">{$selectedMededeling->getTitel()|escape:'html'}</div>
			{if $selectedMededeling->isVerborgen()}
				<em>[verborgen]</em><br />
			{/if}
				<img class="nieuwsplaatje" src="{$csr_pics}nieuws/{$selectedMededeling->getPlaatje()}" width="200px" height="200px" alt="{$selectedMededeling->getPlaatje()}" />
				<i>{$selectedMededeling->getDatum()}</i><br />
				{$ubb->getHTML($selectedMededeling->getTekst())}<br />
			</div>
		{if $selectedMededeling->magBewerken()}
			<a href="{$nieuws_root}bewerken/{$selectedMededeling->getId()}">
				{icon get="bewerken"}
			</a>
			<a href="{$nieuws_root}verwijderen/{$selectedMededeling->getId()}" onclick="return confirm('Weet u zeker dat u deze mededeling wilt verwijderen?');">
				{icon get="verwijderen"}
			</a>
			{if $selectedMededeling->isModerator() AND $selectedMededeling->getZichtbaarheid()=='wacht_goedkeuring'}
			<a onclick="return confirm('Weet u zeker dat u deze mededeling wilt goedkeuren?')" href="{$nieuws_root}keur-goed/{$selectedMededeling->getId()}">
				{icon get="goedkeuren"}
			</a>
			{/if}
		{/if}
		</div>
	{/if}
	
	{* Het Topmost block *}
	<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="mededeling-grotebalk">
			<div class="plaatje">
				<a href="{$nieuws_root}{$mededeling->getId()}">
					<img src="{$csr_pics}nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
 				</a>
			</div>
			<div class="titel">
				<a href="{$nieuws_root}{$mededeling->getId()}">
					{$mededeling->getAfgeknipteTitel()}
 				</a>
 			</div>
			<div class="bericht">{$mededeling->getAfgeknipteTekst()}</div>
		</div>
	{/foreach}
	</div>
</div>