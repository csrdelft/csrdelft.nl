<div class="bb-block bb-peiling" id="peiling{$peiling->getId()}">
	{if $beheer AND $peiling->magBewerken()}
		<a href="/tools/peilingbeheer.php?action=verwijder&amp;id={$peiling->getId()}" class="btn beheer" >Verwijder</a>
	{/if}
	{if $peiling->getStemmenAantal() > 0}
		<div class="totaal">({$peiling->getStemmenAantal()} stem{if $peiling->getStemmenAantal()!=1}men{/if})</div>
	{/if}
	<h2>
		{if $peiling->magBewerken()}<a href="/tools/peilingbeheer.php">#{$peiling->getId()} {/if}
			{$peiling->getTitel()|escape:'html'}
			{if $peiling->magBewerken()}</a>{/if}
	</h2>
	<div class="vraag">{$peiling->getTekst()}</div>
	{if $peiling->magStemmen()}
		<form id="peilingForm{$peiling->getId()}" action="/tools/peilingbeheer.php?action=stem" method="post">
			<input type="hidden" name="id" value="{$peiling->getId()}"/>
	{/if}
			<ul class="peilingopties">
				{foreach from=$peiling->getOpties() item=optie}
					<li>
						{if $peiling->magStemmen()}
							<input type="radio" name="optie" value="{$optie.id}" id="optie{$optie.id}" />
							<label for="optie{$optie.id}" id="label{$optie.id}">{$optie.optie}</label>
						{else}
							<div class="optie">{$optie.optie}</div>
							<div class="stemmen">({$optie.stemmen})</div>
							<div class="percentage">{$optie.percentage|string_format:'%01.1f'}%</div>
							<div class="grafisch"><div class="balk" style="width: {$optie.percentage|string_format:'%d'}%;">&nbsp;</div></div>
						{/if}
					</li>
				{/foreach}
			</ul>
			<br /><br />
	{if $peiling->magStemmen()}
			<input type="button" value="Stem" onclick="peiling_bevestig_stem('#peilingForm{$peiling->getId()}');" />
		</form>
	{/if}
</div>
