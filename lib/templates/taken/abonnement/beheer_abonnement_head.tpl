{*
	beheer_abonnement_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th style="vertical-align: bottom;">Lid</th>
	{foreach from=$repetities item=repetitie}
		<th style="text-align: center;">{strip}
			<a href="/actueel/taken/maaltijdrepetities/beheer/{$repetitie->getMaaltijdRepetitieId()}" title="Wijzig maaltijdrepetitie" class="knop get">{icon get="calendar_edit"}</a>
			<div style="display: inline-block; vertical-align: bottom; width: 20px; height: 140px;">
				<div class="vertical" style="position: relative; top: 120px; font-weight: normal;">
					<nobr>{$repetitie->getStandaardTitel()}</nobr>
				</div>
			</div>
		</th>{/strip}
	{/foreach}
	</tr>
</thead>