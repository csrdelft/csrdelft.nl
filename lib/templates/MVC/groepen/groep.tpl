<hr>
<div id="groep{$groep->id}">
	<div class="groepledenContainer">
		{if LoginLid::mag('P_LEDEN_READ')}
			<ul id="tabs">
				<li id="{GroepTab::Lijst}" class="tab active" onclick="return showTab({$groep->id}, '{GroepTab::Lijst}');" title="Lijst en opmerking tonen">
					<img src="{$CSR_PICS}/knopjes/lijst.png" />
				</li>
				<li id="{GroepTab::Pasfotos}" class="tab" onclick="return showTab({$groep->id}, '{GroepTab::Pasfotos}');" title="Pasfoto's tonen">
					<img src="{$CSR_PICS}/knopjes/pasfoto.png" />
				</li>
				<li id="{GroepTab::Statistiek}" class="tab" onclick="showTab({$groep->id}, '{GroepTab::Statistiek}');" title="Statistiek tonen">
					%
				</li>
				<li id="{GroepTab::Emails}" class="tab" onclick="showTab({$groep->id}, '{GroepTab::Emails}');" title="E-mail's tonen">
					@
				</li>
			</ul>
		{/if}
		{*
		we laden het juiste tabje adh van de hashtag, als er niets
		ingesteld is kiezen we tussen pasfoto's en ledenlijst aan de hand
		van de instelling van de gebruiker.
		*}
		<script type="text/javascript">
			$(document).ready(new function() {
				if (window.location.hash === '{GroepTab::Lijst}' || window.location.hash === '{GroepTab::Pasfotos}' || window.location.hash === '{GroepTab::Statistiek}' || window.location.hash === '{GroepTab::Emails}') {
					showTab('{$groep->id}', window.location.hash.substring(1));
				}
				else {
			{if LidInstellingen::get('groepen', 'toonPasfotos') == 'ja'}
					showTab('{$groep->id}', '{GroepTab::Pasfotos}');
			{/if}
				}
			});
		</script>
		<div id="ledenvangroep{$groep->id}" class="groepleden">
			<table class="leden">
				{foreach from=$lidforms key=uid item=form}
					<tr>
						<td>{$uid|csrnaam:'civitas':'visitekaartje'}</td>
						<td>{$form->view()}</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
	<h2>
		<a href="{Instellingen::get('groepen', 'url')}/{$groep->id}">
			{$groep->naam}
		</a>
	</h2>
	{$groep->samenvatting|ubb}
</div>