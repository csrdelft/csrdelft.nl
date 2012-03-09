<div id="wijzigstatus" >
	<div id="profielregel">
		<div class="naam">
			{if $melding!=''}{$melding}<br />{/if}
			<h1>Lidstatus wijzigen</h1>
			<div class="lidgegevens">
				<label for="">Naam:</label>{$profiel->getUid()|csrnaam:'full'}<br />
				<label for="">Huidige status:</label>{$profiel->getStatus()->getDescription()}
			</div>
		</div>
	</div>

	<p>
		Na het selecteren van lidstatus verschijnen de juiste velden, die u verder mag aanpassen. Bij opslaan worden ook overbodige gegevens verwijderd en abonnementen uitgezet, onomkeerbaar, opletten dus!
	</p>
	{$profiel->getFormulier()->view()}
	
	{*<form action="/communicatie/profiel/{$profiel->getUid()}/{$actie}/" id="statusForm" class="form" method="post">
		{foreach from=$profiel->getFields('formStatus') item=field}
			{$field->view()}
			{if $field->getName()=='postfix'}
				<div class="novieten">
					{if $gelijknamigenovieten|@count>1 OR ($profiel->getStatus()!='S_NOVIET' AND $gelijknamigenovieten|@count>0)}
						Gelijknamige novieten:
						<ul class="nobullets">
								{foreach from=$gelijknamigenovieten item=uid name=novieten}
									<li>{$uid.uid|csrnaam:'civitas'}</li>
								{/foreach}
						</ul>
					{else}
						Geen novieten met overeenkomstige namen.
					{/if}
				</div>
				<div class="leden">
					{if $gelijknamigeleden|@count>1 OR (!($profiel->getStatus()=='S_LID' OR $profiel->getStatus()=='S_GASTLID') AND $gelijknamigenovieten|@count>0)}
					Gelijknamige (gast)leden:
					<ul class="nobullets">
						{foreach from=$gelijknamigeleden item=uid name=leden}
							<li>{$uid.uid|csrnaam:'civitas'}</li>
						{/foreach}
					</ul>
					{else}
						Geen (gast)leden met overeenkomstige namen.
					{/if}
				</div>
			{/if}
		{/foreach}<br />
		<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
			<input type="reset" value="reset formulier" />
			<a class="knop" href="/communicatie/profiel/{$profiel->getUid()}">Annuleren</a>
		</div>
	</form>*}
</div>
