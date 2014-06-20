<div id="groep{$groep->id}" class="groep">
	<div style="float: left;">
		<h2>
			<a href="{Instellingen::get('groepen', 'url')}/{$groep->id}">
				{$groep->naam}
			</a>
		</h2>
		{$groep->samenvatting|ubb}
		{if isset($generaties)}
			<div class="generaties">
				<ul class="nobullets">
					<li class="volgendeGroep"><a href="{Instellingen::get('groepen', 'url')}/{$generaties.volgende->id}">{$generaties.volgende}</a></li>
					<li class="huidigeGroep">{$generaties.huidig}</li>
					<li class="vorigeGroep"><a href="{Instellingen::get('groepen', 'url')}/{$generaties.vorige->id}"{$generaties.vorige}</a></li>
				</ul>
			</div>
		{/if}
	</div>
	<div style="float: right;">
		<ul class="tabs nobullets">
			<li>
				<a class="knop get active" href="{Instellingen::get('groepen', 'url')}/{GroepTab::Lijst}/{$groep->id}" title="Lijst en opmerking tonen">
					<img src="{$CSR_PICS}/knopjes/lijst.png" width="20" height="20" />
				</a>
			</li>
			<li>
				<a class="knop get" href="{Instellingen::get('groepen', 'url')}/{GroepTab::Pasfotos}/{$groep->id}" title="Pasfoto's tonen">
					<img src="{$CSR_PICS}/knopjes/pasfoto.png" width="18" height="18" />
				</a>
			</li>
			<li>
				<a class="knop get" href="{Instellingen::get('groepen', 'url')}/{GroepTab::Statistiek}/{$groep->id}" title="Statistiek tonen">
					%
				</a>
			</li>
			<li><a class="knop get" href="{Instellingen::get('groepen', 'url')}/{GroepTab::Emails}/{$groep->id}" title="E-mail's tonen">
					@
				</a>
			</li>
		</ul>
		<div id="groepleden{$groep->id}" class="groepleden">
			{foreach from=$lidforms key=uid item=form}
				<div class="lid">{$uid|csrnaam:'civitas':'visitekaartje'}</div>
				<div class="opmerking">{$form->view()}</div>
			{/foreach}
		</div>
	</div>
</div>