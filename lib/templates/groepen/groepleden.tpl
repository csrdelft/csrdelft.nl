{if isset($actie) and $actie=='pasfotos'}
	{assign var='meerisopen' value='false'}
	{foreach from=$groep->getLidObjects() item=groeplid name=pasfotos}

		{if $smarty.foreach.pasfotos.index==20}
			<a class="toonmeer" onclick="$('#meerLeden-{$groep->getId()}').toggle();
						this.parentNode.removeChild(this)">
				Nog {$smarty.foreach.pasfotos.total-20} leden tonen...
			</a>
			<div class="verborgen" id="meerLeden-{$groep->getId()}">
				{assign var='meerisopen' value='true'}
			{/if}

			<div class="pasfoto">{$groeplid->getNaamLink('pasfoto', 'link')}</div>

		{/foreach}
		{if $meerisopen === 'true'}
		</div>
	{/if}
{else}

	<table class="leden">
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas':'visitekaartje'}</td>
				{if $groep->magBewerken() OR (LoginModel::getUid()==$groeplid.uid AND ($groep->getToonFuncties()=='tonen' OR $groep->getToonFuncties()=='verbergen'))}
					<td id="bewerk_{$groep->getId()}_{$groeplid.uid}" class="inline_edit">
						<span class="text">
							{foreach from=$groeplid.functie item=glfunctie name=glfunctie}
								{if $smarty.foreach.glfunctie.iteration > 1} - {/if}{$glfunctie|escape:'html'}
							{/foreach}
						</span>
						{if $groep->hasFunctiefilter()}
							{foreach from=$groep->getFunctiefilters() item=filter name=filter}
								<select name="functie[]" class="editbox" id="functie_input_{$groep->getId()}{$groeplid.uid}">
									{foreach from=$filter item=filteroption}
										<option value="{$filteroption|escape:'html'}" {if $filteroption==$groeplid.functie[$smarty.foreach.filter.index]}selected="selected"{/if}>{$filteroption|escape:'html'}</option>
									{/foreach}
								</select>
							{/foreach}
						{else}
							<input type="text" maxlength="25" 
								   value="{foreach from=$groeplid.functie item=glfunctie name=glfunctie}{if $smarty.foreach.glfunctie.iteration > 1} - {/if}{$glfunctie|escape:'html'}{/foreach}"
								   class="editbox"  />
						{/if}
					</td>
				{else}	
					{if $groep->toonFuncties()}
						<td><em>
								{foreach from=$groeplid.functie item=glfunctie name=glfunctie}
									{if $smarty.foreach.glfunctie.iteration > 1} - {/if}{$glfunctie|escape:'html'}
								{/foreach}
							</em></td>
						{/if}
					{/if}
					{if $groep->magBewerken() OR LoginModel::getUid()==$groeplid.uid}
					<td>
						{if in_array($groep->getTypeId(), array(2, 3)) AND $groep->getStatus()=='ht'}{* maak lid ot voor huizen/onderverenigingen. Dit kunnen leden ook bij zichzelf doen. *}
								<a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/maakLidOt/{$groeplid.uid}" title="Verplaats lid naar o.t.-groep" 
								   {if !$groep->isAdmin()}onclick="return confirm('Weet u zeker dat u deze bewoner naar de oudbewonersgroep wilt verplaatsen?');"{/if}>
									&raquo;
								</a>
							{/if}
							{if $groep->isAdmin() OR $groep->isEigenaar() OR $groeplid.uid!=LoginModel::getUid()} {* We kunnen onzelf niet uit een groep gooien gooien *}
									<a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/verwijderLid/{$groeplid.uid}" title="Verwijder lid uit groep" onclick="return confirm('Weet u zeker dat u dit groeplid wilt verwijderen?');">X</a>
								{/if}
							</td>
						{/if}
					</tr>
					{/foreach}
					</table>
					{/if}
						{* We geven nog even even een aanmeldding weer als de groep aanmeldbaar is. *}
						{if $groep->isAanmeldbaar() AND !$groep->isLid() AND LoginModel::mag('P_LOGGED_IN')}
							<div class="aanmelden">
								{if $groep->magAanmelden()}
									{if $groep->getToonFuncties()=='niet' OR $groep->getToonFuncties()=='tonenzonderinvoer'}
										<a  {if !isset($actie) or $actie!='pasfotos'}class="knop"{/if} href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/aanmelden" onclick="if(confirm('Weet u zeker dat u zich wilt aanmelden?')) { return true; } else { event.preventDefault(); } ;">
											{if isset($actie) and $actie=='pasfotos'}
												<img class="pasfoto" style="width: auto; height: 100px;" src="{$CSR_PICS}/groepen/aanmelden.jpg" title="Aanmelden voor deze groep"
													 onmouseover="this.src = '/tools/pasfotos.php?image';" onmouseout="this.src = '{$CSR_PICS}/groepen/aanmelden.jpg';" />
											{else}
												Aanmelden voor deze groep
											{/if}
										</a>
									{if $groep->getVrijeplaatsen()!=0}<br />{/if}{* nog-vrije-plaatsen-melding *}
								{else}
									<form action="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/aanmelden" method="post" id="aanmeldForm" class="clear">
										<strong>Aanmelden</strong><br />
										{if $groep->hasFunctiefilter()}
											{foreach from=$groep->getFunctiefilters() item=filter}
												<select name="functie[]">
													{foreach from=$filter item=filteroption}
														<option value="{$filteroption|escape:'html'}">{$filteroption|escape:'html'}</option>
													{/foreach}
												</select>
											{/foreach}
										{else}
											<input type="text" name="functie" maxlength="60" class="functie" />
										{/if}&nbsp;<input type="submit" value="aanmelden" onclick="event.preventDefault(); this.form.submit(); return false;" />
									</form>

								{/if}
								{if $groep->getVrijeplaatsen()!=0}nog {$groep->getVrijeplaatsen()} plaatsen vrij{/if}
							{elseif $groep->isVol()}
								Deze groep is vol, u kunt zich niet meer aanmelden.
							{/if}
						</div>
						{/if}
