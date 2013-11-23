{*
	beheer_abonnement_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th style="vertical-align: bottom;">Lid</th>
	{foreach from=$repetities item=repetitie}
		<th style="width: 30px; background-color: {cycle values="#F0F0F0,#FAFAFA"};">{strip}
			<div style="width: 28px;">
				<a href="/actueel/taken/maaltijdrepetities/beheer/{$repetitie->getMaaltijdRepetitieId()}" title="Wijzig maaltijdrepetitie" class="knop get">
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