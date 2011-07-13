{if $actie=='pasfotos'}
	<div class="pasfotomatrix">
		{foreach from=$groep->getLidObjects() item=groeplid name=pasfotos}
			{if $smarty.foreach.pasfotos.index==20}
				<a class="toonmeer handje" onclick="toggleDiv('meerLeden-{$groep->getId()}'); this.parentNode.removeChild(this)">
					Nog {$smarty.foreach.pasfotos.total-20} leden tonen...
				</a>
				<div class="verborgen" id="meerLeden-{$groep->getId()}">
				{assign var='meerisopen' value='true'}
			{/if}

			{if $groep->isIngelogged()}
				<a href="/communicatie/profiel/{$groeplid->getUid()}" title="{$groeplid->getNaam()}">
			{/if}
			{$groeplid->getPasfoto(true)}
			{if $groep->isIngelogged()}
				</a>
			{/if}

			{if $smarty.foreach.pasfotos.last AND $meerisopen}
				</div>
			{/if}
		{/foreach}
	</div>
{else}

	<table class="leden">
		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				{if $groep->magBewerken() OR ($loginlid->getUid()==$groeplid.uid AND ($groep->getToonFuncties()=='tonen' OR $groep->getToonFuncties()=='verbergen'))}
					<td  id="{$groep->getId()}{$groeplid.uid}" class="edit_td">
						<span id="functie_{$groep->getId()}{$groeplid.uid}" class="text">{$groeplid.functie|escape:'html'}</span>
						{if $groep->hasFunctiefilter()}
							<select name="functie" class="editbox" id="functie_input_{$groep->getId()}{$groeplid.uid}">
								{foreach from=$groep->getFunctiefilters() item=filter}
									<option value="{$filter|escape:'html'}" {if $filter==$groeplid.functie}selected="selected"{/if}>{$filter|escape:'html'}</option>
								{/foreach}
							</select>
						{else}
							<input type="text" maxlength="25" value="{$groeplid.functie|escape:'html'}" class="editbox" id="functie_input_{$groep->getId()}{$groeplid.uid}" />
						{/if}
						<input type="hidden" value="{$groeplid.uid}" id="uid_{$groep->getId()}{$groeplid.uid}"/>
						<input type="hidden" value="{$groep->getId()}" id="gid_{$groep->getId()}{$groeplid.uid}"/>
					</td>
				{else}	
					{if $groep->toonFuncties()}<td><em>{$groeplid.functie|escape:'html'}</em></td>{/if}
				{/if}
				{if $groep->magBewerken() OR $loginlid->getUid()==$groeplid.uid}
					<td>
					{if $groep->getTypeId()==2 AND $groep->getStatus()=='ht'}{* maak lid ot voor huizen. Dit kunnen leden ook bij zichzelf doen. *}
						<a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/maakLidOt/{$groeplid.uid}" title="Verplaats lid naar o.t.-groep" 
							{if !$groep->isAdmin()}onclick="return confirm('Weet u zeker dat u deze bewoner naar de oudbewonersgroep wilt verplaatsen?')"{/if}>
							&raquo;
						</a>
					{/if}
					{if $groep->isAdmin() OR $groep->isEigenaar() OR $groeplid.uid!=$loginlid->getUid()} {* We kunnen onzelf niet uit een groep gooien gooien *}
						<a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/verwijderLid/{$groeplid.uid}" title="Verwijder lid uit groep">X</a>
					{/if}
					</td>
				{/if}
			</tr>
		{/foreach}
	</table>
{/if}
{* We geven nog even even een aanmeldding weer als de groep aanmeldbaar is. *}
{if $groep->isAanmeldbaar() AND !$groep->isLid() AND $loginlid->hasPermission('P_LOGGED_IN')}
	<div class="aanmelden">
		{if $groep->magAanmelden()}
			{if $groep->getToonFuncties()=='niet' OR $groep->getToonFuncties()=='tonenzonderinvoer'}
				<a  {if $actie!='pasfotos'}class="knop"{/if} href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/aanmelden" onclick="return confirm('Weet u zeker dat u zich wilt aanmelden?')">
					{if $actie=='pasfotos'}
						<img class="pasfoto" src="{$csr_pics}/groepen/aanmelden.jpg" title="Aanmelden voor deze groep"
							onmouseover="this.src='/tools/pasfotos.php?image';" onmouseout="this.src='{$csr_pics}/groepen/aanmelden.jpg';" />
					{else}
						Aanmelden voor deze groep
					{/if}
				</a>
				{if $groep->getVrijeplaatsen()!=0}<br />{/if}{* nog-vrije-plaatsen-melding *}
			{else}
				<form action="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/aanmelden" method="post" id="aanmeldForm" class="clear">
					<strong>Aanmelden</strong><br />
					{if $groep->hasFunctiefilter()}
						<select name="functie">
							{foreach from=$groep->getFunctiefilters() item=filter}
								<option value="{$filter|escape:'html'}">{$filter|escape:'html'}</option>
							{/foreach}
						</select>
					{else}
						<input type="text" name="functie" maxlength="25" class="functie" />
					{/if}&nbsp;<input type="submit" value="aanmelden" />
				</form>
				
			{/if}
			{if $groep->getVrijeplaatsen()!=0}nog {$groep->getVrijeplaatsen()} plaatsen vrij{/if}
		{elseif $groep->isVol()}
			Deze groep is vol, u kunt zich niet meer aanmelden.
		{/if}
	</div>
{/if}
