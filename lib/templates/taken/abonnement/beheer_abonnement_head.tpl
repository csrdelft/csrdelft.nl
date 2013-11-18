{*
	beheer_abonnement_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th>Lid</th>
	{foreach from=$repetities item=repetitie}
		<th>
			{$repetitie->getStandaardTitel()}
			&nbsp;<a href="/actueel/taken/maaltijdrepetities/beheer/{$repetitie->getMaaltijdRepetitieId()}" title="Wijzig maaltijdrepetitie" class="knop get">{icon get="calendar_edit"}</a>
		</th>
	{/foreach}
	</tr>
</thead>