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
<div style="height: 250px; overflow: auto;">
	<table id="suggesties-tabel" class="taken-tabel" style="padding: 0px;">
		<tbody>
		{foreach name="tabel" from=$suggesties key=uid item=suggestie}
			<tr class="
				{if !$suggestie.voorkeur} geenvoorkeur{/if}
				{if $suggestie.recent} recent{/if}
				{if $this->getIsJongsteLichting($uid)} jongste{/if}	
				">
				<td style="width: 20px;">
					<a class="knop" onclick="$('#field_lid_id').val('{$uid}');$('#taken-taak-toewijzen-form').submit();">
					{if $suggestie.recent}
						{icon get="time_delete" title="Recent gecorveed"}
					{elseif $suggestie.voorkeur}
						{icon get="emoticon_smile" title="Heeft voorkeur"}
					{else}
						{icon get="bullet_go" title="Toewijzen aan dit lid"}
					{/if}
					</a>
				</td>
				<td style="width: 20px;">
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
<br />
{/strip}