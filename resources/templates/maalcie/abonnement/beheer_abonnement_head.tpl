{*
	beheer_abonnement_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th class="text-bottom">Lid</th>
	{foreach from=$repetities item=repetitie}
		<th class="{cycle values="rowColor0,rowColor1"}" style="width: 30px;">{strip}
			<div style="width: 28px;">
				<a href="/maaltijdenrepetities/beheer/{$repetitie->mlt_repetitie_id}" title="Wijzig maaltijdrepetitie" class="btn popup">
					{icon get="calendar_edit"}
				</a>
			</div>
			<div style="width: 26px; height: 140px;">
				<div class="vertical niet-dik" style="position: relative; top: 120px;">
					<nobr>{$repetitie->standaard_titel}</nobr>
				</div>
			</div>
		</th>{/strip}
	{/foreach}
	</tr>
</thead>