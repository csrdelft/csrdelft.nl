{*
	instellingen_page.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<table style="width: 100%;"><tr id="maalcie-melding"><td id="maalcie-melding-veld">{SimpleHtml::getMelding()}</td></tr></table>
<h1>{$titel}</h1>
<p>
	Op deze pagina kunt u instellingen wijzigen en resetten voor elke module op de stek.
	Onderstaande tabel toont alle instellingen van de gekozen module.
</p>
<p>
	N.B. Deze instellingen zijn essentieel voor de werking van de stek!
</p>
<div style="float: right;">
	<div style="display: inline-block;"><label for="toon">Toon module:</label>
	</div><select name="toon" onchange="location.href = '/instellingenbeheer/module/' + this.value;">
		<option selected="selected">kies</option>
		{foreach from=$modules item=module}
			<option value="{$module}">{$module}</option>
		{/foreach}
	</select>
</div>
<br />
{if $instellingen}
	<table id="maalcie-tabel" class="maalcie-tabel">
		<thead>
			<tr>
				<th>Wijzig</th>
				<th>Id</th>
				<th>Waarde</th>
				<th>Reset</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$instellingen item=instelling}
				{include file='MVC/instellingen/beheer/instelling_row.tpl' instelling=$instelling}
			{/foreach}
		</tbody>
	</table>
{/if}