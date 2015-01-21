<div id="groep-{$groep->id}" class="groep">
	<div class="float-left">
		<h3>
			<a href="{groepenUrl}{$groep->id}">
				{$groep->naam}
			</a>
		</h3>
		{$groep->samenvatting|bbcode}
		{if isset($generaties)}
			<div class="generaties">
				<ul class="nobullets">
					<li class="volgendeGroep"><a href="{groepenUrl}{$generaties.volgende->id}">{$generaties.volgende}</a></li>
					<li class="huidigeGroep">{$generaties.huidig}</li>
					<li class="vorigeGroep"><a href="{groepenUrl}{$generaties.vorige->id}">{$generaties.vorige}</a></li>
				</ul>
			</div>
		{/if}
	</div>
	<div class="float-right">
		<ul class="tabs nobullets">
			<li>
				<a class="btn post{if $tab === GroepTab::Lijst} active{/if}" href="{groepenUrl}{$groep->id}/{GroepTab::Lijst}" title="Lijst en opmerking tonen">
					<img src="/plaetjes/knopjes/lijst.png" width="20" height="20" />
				</a>
			</li>
			<li>
				<a class="btn post{if $tab === GroepTab::Pasfotos} active{/if}" href="{groepenUrl}{$groep->id}/{GroepTab::Pasfotos}" title="Pasfoto's tonen">
					<img src="/plaetjes/knopjes/pasfoto.png" width="18" height="18" />
				</a>
			</li>
			<li>
				<a class="btn post{if $tab === GroepTab::Statistiek} active{/if}" href="{groepenUrl}{$groep->id}/{GroepTab::Statistiek}" title="Statistiek tonen">
					%
				</a>
			</li>
			<li><a class="btn post{if $tab === GroepTab::Emails} active{/if}" href="{groepenUrl}{$groep->id}/{GroepTab::Emails}" title="E-mail's tonen">
					@
				</a>
			</li>
		</ul>
		<div id="groepTabContent-{$groep->id}" class="groepTabContent">
			{$tabContent->view()}
		</div>
	</div>
</div>