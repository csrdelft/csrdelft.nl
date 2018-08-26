<div class="mededelingen-overzichtlijst">
	{foreach from=$lijst key=groepering item=mededelingen}
		<div class="mededelingenlijst-block">
			<div class="mededelingenlijst-block-titel">{$groepering|ucfirst}</div>
			{foreach from=$mededelingen item=mededeling}
				<div {if $mededeling->id==$geselecteerdeMededeling->id}id="actief" {/if}class="mededelingenlijst-item{if $mededeling->zichtbaarheid == 'onzichtbaar'} verborgen-item{/if}{if $mededeling->zichtbaarheid == 'verwijderd'} verwijderd-item{/if}">
					{if $mededeling->getCategorie()->plaatje !=''}
						<div class="mededelingenlijst-plaatje">
							<a href="{$pagina_root}{$mededeling->id}">
								<img src="/plaetjes/nieuws/{$mededeling->getCategorie()->plaatje}" width="10px" height="10px" />
							</a>
						</div>
					{/if}
					<div class="itemtitel">
						{* {$mededeling->getDatum()} *}
						<a href="{$pagina_root}{$mededeling->id}"{toegang P_NEWS_MOD} class="{if !$mededeling->prive}cursief{/if} {if $mededeling->zichtbaarheid=='wacht_goedkeuring'}dikgedrukt{/if}"{/toegang}>
							{$mededeling->titel|bbcode|html_substr:"40":"…"}
						</a>
					</div>
				</div>
			{/foreach}
		</div> {* Einde mededelingenlijst-block*}
	{/foreach}
	<div class="mededelingen_paginering">
		Pagina: {sliding_pager baseurl="`$pagina_root`pagina/"
					pagecount=$totaalAantalPaginas curpage=$huidigePagina
					separator="" show_always=true}
	</div>

</div> {* Einde mededelingen-overzichtlijst *}
