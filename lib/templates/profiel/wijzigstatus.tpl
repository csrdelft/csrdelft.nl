<div id="wijzigstatus" >
	<div id="profielregel">
		<div class="naam">
			{getMelding()}
			<h1>Lidstatus wijzigen</h1>
			<div class="lidgegevens">
				<label for="">Naam:</label>{$profiel->getUid()|csrnaam:'volledig'}<br />
				<label for="">Huidige status:</label>{$profiel->getStatus()->getDescription()}
			</div>
		</div>
	</div>

	<p>
		Na het selecteren van lidstatus verschijnen de juiste velden, die u verder mag aanpassen. Bij opslaan worden ook overbodige <span class="onderstreept">gegevens verwijderd</span> en <span class="onderstreept">abonnementen uitgezet</span>, onomkeerbaar, opletten dus!
	</p>

	{$profiel->getFormulier()->view()}

	<script type="text/javascript">{literal}$("#postfix").after(
		{/literal}'<div class="novieten">'+
			{if $gelijknamigenovieten|@count>1 OR ($profiel->getStatus()!='S_NOVIET' AND $gelijknamigenovieten|@count>0)}
				'Gelijknamige novieten:'+
				'<ul class="nobullets">'+
						{foreach from=$gelijknamigenovieten item=uid name=novieten}
							'<li>{$uid.uid|csrnaam:"civitas"}</li>'+
						{/foreach}
				'</ul>'+
			{else}
				'Geen novieten met overeenkomstige namen.'+
			{/if}
		'</div>'+
		'<div class="leden">'+
			{if $gelijknamigeleden|@count>1 OR (!($profiel->getStatus()=='S_LID' OR $profiel->getStatus()=='S_GASTLID') AND $gelijknamigenovieten|@count>0)}
			'Gelijknamige (gast)leden:'+
			'<ul class="nobullets">'+
				{foreach from=$gelijknamigeleden item=uid name=leden}
					'<li>{$uid.uid|csrnaam:"civitas"}</li>'+
				{/foreach}
			'</ul>'+
			{else}
				'Geen (gast)leden met overeenkomstige namen.'+
			{/if}
		'</div>'{literal}
	);{/literal}</script>

</div>
