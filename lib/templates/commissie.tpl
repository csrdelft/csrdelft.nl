<div style="float: right; width: 210px; margin: 10px;">
	<strong>Commissieleden:</strong>
	<table>
		{foreach from=$aCieLeden item=cieLid}
			<tr>
				<td width="150px">{$cieLid.uid|csrnaam:'civitas'}</td>
				<td>{$cieLid.functie|escape:'html'}</td>
				{if $magBewerken}
					<td><a href="/groepen/commissie/{$cie.id}/verwijder/lid/{$cieLid.uid}">X</a></td>
				{/if}
			</tr>
		{/foreach}
	</table>
</div>
<h2>{$cie.titel}</h2>
{if $magBewerken AND $action=='edit'}
	<form action="/groepen/commissie/{$cie.id}/bewerken" method="post">
	<div class="cieAdmin" style="width: 100%; clear: both;">
		<h2>Commissie bewerken:</h2>
		<strong>Website:</strong><br />
		<input type="text" name="link" value="{$cie.link|escape:'html'}" style="width: 100%" />
		<strong>Korte beschrijving:</strong><br />
		<textarea name="stekst" style="width: 100%">{$cie.stekst|escape:'html'}</textarea>
		<strong>Lange beschrijving:</strong><br />
		<textarea name="tekst" style="width: 100%; height: 200px;">{$cie.tekst|escape:'html'}</textarea>
		<input type="submit" value="Opslaan" /> <a href="/groepen/commissies/{$cie.id}/" class="knop">terug</a>
	</div>
	</form>
{else}
	<div style="float: right; margin: 10px ;"><a href="/groepen/commissies/{$cie.id}/bewerken" class="knop">bewerken</a></div>
	{$cie.tekst|ubb}
	
	{if $cie.link!=''}
		<br /> <br />Commissiewebstek: <a href="{$cie.link|escape:'html'}">{$cie.link}</a>	
	{/if}
	
	{if $magBewerken}
		<div style="clear: both;"></div><hr />
		<h2>Deze commissie beheren:</h2>
		<br />
		<form action="/groepen/commissie/{$cie.id}" method="post">
			{if $lidAdder!==false}
				{$lidAdder}
			{else}
				Geef hier namen of lidnummers op voor deze commissie, gescheiden door komma's<br />
				<input type="text" name="cieNamen" class="tekst" />
			{/if}
			<input type="submit" value="Verzenden" />
		</form>
	{/if}
{/if}



