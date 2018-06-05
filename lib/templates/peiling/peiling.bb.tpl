<div class="bb-block bb-peiling" id="peiling{$peiling->id}">
	{if $beheer AND $peiling->magBewerken()}
		<a href="/peilingen/verwijderen/{$peiling->id}" class="btn beheer" >Verwijder</a>
	{/if}
	{if $peiling->getStemmenAantal() > 0}
		<span class="totaal">({$peiling->getStemmenAantal()} stem{if $peiling->getStemmenAantal()!=1}men{/if})</span>
	{/if}
	<h3>
		{if $peiling->magBewerken()}<a href="/peilingen/beheer">#{$peiling->id}{/if}
			{$peiling->titel|escape:'html'}
		{if $peiling->magBewerken()}</a>{/if}
	</h3>
	<div class="vraag">{$peiling->tekst}</div>
	{if $peiling->magStemmen()}
		<form id="peilingForm{$peiling->id}" action="/peilingen/stem" method="post">
			<input type="hidden" name="id" value="{$peiling->id}"/>
	{/if}
			<ul class="peilingopties">
				{foreach from=$peiling->getOpties() item=optie}
					<li>
						{if $peiling->magStemmen()}
							<input type="radio" name="optie" value="{$optie->id}" id="optie{$optie->id}" />
							<label for="optie{$optie->id}" id="label{$optie->id}">{$optie->optie}</label>
						{else}
							{assign var="percentage" value=$optie->stemmen/$peiling->getStemmenAantal()*100}
							<div class="optie">{$optie->optie}</div>
							<div class="stemmen">({$optie->stemmen})</div>
							<div class="percentage">{$percentage|string_format:'%01.1f'}%</div>
							<div class="grafisch"><div class="balk" style="width: {$percentage|string_format:'%d'}%;">&nbsp;</div></div>
						{/if}
					</li>
				{/foreach}
			</ul>
			<br /><br />
	{if $peiling->magStemmen()}
			<input type="button" value="Stem" onclick="window.peiling.peilingBevestigStem('#peilingForm{$peiling->id}');" />
		</form>
	{/if}
</div>
