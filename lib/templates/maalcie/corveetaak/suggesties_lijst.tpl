{*
mijn_rooster.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
	<br />
	<div id="suggesties" style="border: 1px solid #A9A9A9; ">
		<table class="maalcie-tabel" style="padding: 0;">
			<thead>
				<tr>
					<th style="width: 65px; padding-right: 0;">
						{if $kwalificatie_benodigd}
							Relatief
						{else}
							Prognose
						{/if}
					</th>
					<th style="width: 135px;">Naam</th>
					<th>Laatste taak
						{if $kwalificatie_benodigd}
							&nbsp;{icon get="bullet_arrow_up"}
						{/if}
					</th>
				</tr>
			</thead>
		</table>
		<div class="scrollpane" id="suggesties-scrollpane" style="height: 250px;">
			<table id="suggesties-tabel" class="maalcie-tabel verborgen">
				<tbody>
					{foreach name="tabel" from=$suggesties key=uid item=suggestie}
						<tr class="
							{if !$suggestie.voorkeur} geenvoorkeur{/if}
							{if $suggestie.recent} recent{/if}
							{if $jongsteLichting === LidCache::getLid($uid)->getLichting()} jongste{else} oudere{/if}
							">
							<td style="width: 15px;">
								<a class="knop" style="padding: 0 2px;" onclick="$('#field_uid').val('{$uid}');
										$('#maalcie-taak-toewijzen-form').submit();">
									{if $suggestie.recent}
										{icon get="time_delete" title="Recent gecorveed"}
									{elseif $suggestie.voorkeur}
										{icon get="emoticon_smile" title="Heeft voorkeur"}
									{else}
										{icon get="bullet_go" title="Toewijzen aan dit lid"}
									{/if}
								</a>
							</td>
							<td style="width: 30px; padding-right: 10px; text-align: right;">
								{if $kwalificatie_benodigd}
									{if $suggestie.relatief > 0}+{/if}
									{$suggestie.relatief}
								{else}
									{$suggestie.prognose}
								{/if}
							</td>
							<td style="width: 140px;">
								{LidCache::getLid($uid)->getNaamLink(Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}
							</td>
							{if $suggestie.laatste}
								<td>{$suggestie.laatste->getBeginMoment()|date_format:"%d %b %Y"}</td>
								<td>{$suggestie.laatste->getCorveeFunctie()->naam}</td>
							{else}
								<td colspan="2"></td>
							{/if}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<table id="suggesties-controls">
		<tr>
			<td {if isset($voorkeurbaar) AND !$voorkeurbaar}
					title="Deze corveerepetitie is niet voorkeurbaar." 
				{elseif !isset($voorkeurbaar)}
					title="Dit is geen periodieke taak dus zijn er geen voorkeuren." 
				{/if}
			>
				<input type="checkbox" id="voorkeur" 
					   {if !isset($voorkeurbaar) OR !$voorkeurbaar}
						   disabled 
					   {else}
						   {if $voorkeur}
							   checked="checked" 
						   {/if}
						   onchange="taken_toggle_suggestie('geenvoorkeur');" 
					   {/if}
					   />
				<label for="voorkeur">Met voorkeur</label>
				<br />
				<input type="checkbox" id="recent" onchange="taken_toggle_suggestie('recent');" 
					   {if $recent}
						   checked="checked" 
					   {/if}
					   />
				<label for="recent">Niet recent gecorveed</label>
			</td>
			<td>
				<p>Toon novieten/sjaars</p>

				<input type="radio" id="jongste_ja" name="jongste" value="ja" onchange="
						taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
						taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
					   " checked="checked" />
				<label for="jongste_ja">Ja</label>

				<input type="radio" id="jongste_nee" name="jongste" value="nee" onchange="
						taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
						taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
					   " />
				<label for="jongste_nee">Nee</label>

				<input type="radio" id="jongste_alleen" name="jongste" value="alleen" onchange="
						taken_toggle_suggestie('oudere', 'alleen' !== $('#jongste_alleen:checked').val());
						taken_toggle_suggestie('jongste', 'nee' !== $('#jongste_nee:checked').val());
					   " />
				<label for="jongste_alleen">Alleen</label>
			</td>
			<td style="width: 25px;">
				<br />
				<a class="knop vergroot" data-vergroot="#suggesties-scrollpane" title="Vergroot de lijst met suggesties">&uarr;&darr;</a>
			</td>
		</tr>
	</table>
{/strip}