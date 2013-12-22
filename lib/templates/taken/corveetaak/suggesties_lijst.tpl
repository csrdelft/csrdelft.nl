{*
	mijn_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<br />
<div style=" border: 1px solid #A9A9A9;">
<table class="taken-tabel" style="padding: 0px;">
	<thead>
		<tr>
			<th style="width: 50px;">
				{if $taak->getCorveeFunctie()->getIsKwalificatieBenodigd()}
					Aantal
				{else}
					Punten
				{/if}
			</th>
			<th style="width: 145px;">Naam</th>
			<th>Laatste taak</th>
		</tr>
	</thead>
</table>
<div id="scrollpane" style="height: 250px; overflow: auto;">
	<table id="suggesties-tabel" class="taken-tabel" style="padding: 0px;">
		<tbody>
		{foreach name="tabel" from=$suggesties key=uid item=suggestie}
			<tr class="
				{if !$suggestie.voorkeur} geenvoorkeur{/if}
				{if $suggestie.recent} recent{/if}
				{if $this->getIsJongsteLichting($uid)} jongste{else} oudere{/if}	
				">
				<td style="width: 20px;">
					<a class="knop" style="padding: 0px 2px;" onclick="$('#field_lid_id').val('{$uid}');$('#taken-taak-toewijzen-form').submit();">
					{if $suggestie.recent}
						{icon get="time_delete" title="Recent gecorveed"}
					{elseif $suggestie.voorkeur}
						{icon get="emoticon_smile" title="Heeft voorkeur"}
					{else}
						{icon get="bullet_go" title="Toewijzen aan dit lid"}
					{/if}
					</a>
				</td>
				<td style="width: 20px; text-align: right;">
					{if $taak->getCorveeFunctie()->getIsKwalificatieBenodigd()}
						{$suggestie.aantal}
					{else}
						 {$suggestie.prognose}
					{/if}
				</td>
				<td style="width: 150px;">
					{$this->getLidnaam($uid)}
				</td>
			{if $suggestie.laatste}
				<td>{$suggestie.laatste->getBeginMoment()|date_format:"%d %b %Y"}</td>
				<td>{$suggestie.laatste->getCorveeFunctie()->getNaam()}</td>
			{else}
				<td colspan="2"></td>
			{/if}
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
</div>
<table style="width: 100%; background-color: #EEEEEE; border: 1px solid #DDDDDD;">
<tr><td style="width: 45%; padding: 7px !important;" 
	{if isset($voorkeur)}
		{if !$voorkeur}
			title="Deze corveerepetitie is niet voorkeurbaar."
		{/if}
	{else}
		title="Dit is geen periodieke taak dus zijn er geen voorkeuren."
	{/if}
	>
	<input type="checkbox" id="voorkeur" 
	{if isset($voorkeur)}
		{if $voorkeur}
			checked="checked" 
			onchange="taken_toggle_suggestie('geenvoorkeur');" 
		{else}
			disabled 
		{/if}
	{else}
		disabled 
	{/if}
	/>
	<label for="voorkeur" style="padding-left: 7px !important; float: none; position: relative; top: -4px;">Met voorkeur</label>
	{if $voorkeur}
		<script type="text/javascript">$(document).ready(function(){ldelim}taken_toggle_suggestie('geenvoorkeur');{rdelim});</script>
	{/if}
</td><td rowspan="2" style="padding: 7px;">
	<p>Toon novieten/sjaars</p>
	
	<input type="radio" id="jongste_ja" name="jongste" value="ja" onchange="taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());" checked="checked" />
	<label for="jongste_ja" style="padding-left: 7px !important; float: none; padding-right: 15px !important; position: relative; top: -4px;">Ja</label>
	
	<input type="radio" id="jongste_nee" name="jongste" value="nee" onchange="taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());" />
	<label for="jongste_nee" style="padding-left: 7px !important; float: none; padding-right: 15px !important; position: relative; top: -4px;">Nee</label>
	
	<input type="radio" id="jongste_alleen" name="jongste" value="alleen" onchange="taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());" />
	<label for="jongste_alleen" style="padding-left: 7px !important; float: none; padding-right: 15px !important; position: relative; top: -4px;">Alleen</label>
</td><td rowspan="3" style="width: 25px;">
	<br />
	<a class="knop" onclick="$('#scrollpane').animate({ldelim}height: '+=250'{rdelim}, 800, function() {ldelim}{rdelim});" title="Vergroot de lijst met suggesties"><strong>&uarr;&darr;</strong></a>
</td></tr>
<tr><td style="padding-left: 7px;">
	<input type="checkbox" id="recent" checked="checked" onchange="taken_toggle_suggestie('recent');" />
	<label for="recent" style="padding-left: 7px !important; float: none; position: relative; top: -4px;">Niet recent gecorveed</label>
	<script type="text/javascript">$(document).ready(function(){ldelim}taken_toggle_suggestie('recent');{rdelim});</script>
</td></tr>
</table>
{/strip}