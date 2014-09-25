{*
	beheer_abonnement_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th style="vertical-align: bottom;">Lid</th>
	{foreach from=$repetities item=repetitie}
		<th style="width: 30px; background-color: {cycle values="#f5f5f5,#FAFAFA"}; color: #000;">{strip}
			<div style="width: 28px;">
				<a href="/maaltijdenrepetities/beheer/{$repetitie->getMaaltijdRepetitieId()}" title="Wijzig maaltijdrepetitie" class="knop popup">
					{icon get="calendar_edit"}
				</a>
			</div>
			<div style="width: 26px; height: 140px;">
				<div class="vertical" style="font-weight: normal; position: relative; top: 120px;">
					<nobr>{$repetitie->getStandaardTitel()}</nobr>
				</div>
			</div>
		</th>{/strip}
	{/foreach}
	</tr>
</thead>