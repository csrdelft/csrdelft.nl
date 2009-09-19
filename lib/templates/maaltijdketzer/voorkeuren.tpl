<ul class="horizontal nobullets">
	<li>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li class="active">
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
	{if $loginlid->hasPermission('P_MAAL_MOD')}
		<li>
			<a href="/actueel/maaltijden/beheer/" title="Beheer">Maaltijdbeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/corveebeheer/" title="Corveebeheer">Corveebeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
		</li>
	{/if}
</ul>
<hr />
<h1>Maaltijd-voorkeuren</h1>
<p>
Hier kunt u enkele instellingen doen voor uw maaltijden. U kunt abonnementen aan en uitzetten, 
en uw eetwens(di&euml;et) aanpassen.

<h2>Eetwens/di&euml;et</h2>
<p>
Het is mogelijk dat u allergisch bent voor bepaalde ingredienten, of dat u uit bepaalde 
overwegingen geen vlees wilt eten. Dit kunt u hieronder aangeven, de koks zullen er dan
rekening mee houden. <strong>Dit is niet de plek om aan te geven dat u iets niet lekker vindt.</strong>
</p>
<form method="post" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="editEetwens" />
	<input type="text" name="eetwens" value="{$maal.eetwens}" size="50" /> <input type="submit" name="opslaan" value="opslaan" />
</form>
<br />

<h2>Corveevoorkeuren</h2>
<p>Hier kunt u voorkeuren opgeven waar am. Corveecaesar rekening mee kan houden bij het indelen
van u bij maaltijden. Kruis minstens een vakje aan.
</p>
<form method="post" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="editCorveevoorkeuren" />
	<input type="checkbox" name="corvee_voorkeuren[ma_kok]" value="1" {if $maal.corvee_voorkeuren.ma_kok}checked="checked"{/if} /> Maandag koken<br />
	<input type="checkbox" name="corvee_voorkeuren[ma_afwas]" value="1" {if $maal.corvee_voorkeuren.ma_afwas}checked="checked"{/if} /> Maandag afwassen<br />
	<input type="checkbox" name="corvee_voorkeuren[do_kok]" value="1" {if $maal.corvee_voorkeuren.do_kok}checked="checked"{/if} /> Donderdag koken<br />
	<input type="checkbox" name="corvee_voorkeuren[do_afwas]" value="1" {if $maal.corvee_voorkeuren.do_afwas}checked="checked"{/if} /> Donderdag afwassen<br />	
	<input type="checkbox" name="corvee_voorkeuren[theedoek]" value="1" {if $maal.corvee_voorkeuren.theedoek}checked="checked"{/if} /> Theedoeken wassen<br />
	<input type="submit" name="opslaan" value="opslaan" />
</form>
<br />

<h2>Maaltijdabonnementen</h2>
<p>
	<table style="width: 100%;">
		<tr>
			<td>
				{if $maal.abo.abos|@count==0}
					Er is geen maaltijdabonnement geactiveerd.
				{else}
					<table style="width: 300px;">
						{foreach from=$maal.abo.abos key=abosoort item=abotekst}
							<tr>
								<td>&#8226; {$abotekst}</td>
								<td>
									<a href="{$smarty.server.PHP_SELF}?a=delabo&abo={$abosoort}">[ uitschakelen ]</a>
								</td>
							</tr>
						{/foreach}
					</table>
				{/if}
			</td>
			<td>
				{if $maal.abo.nietAbos|@count!=0}
					<form action="{$smarty.server.PHP_SELF}" method="POST">
						<input type="hidden" name="a" value="addabo" />
						<label for="addabo_abo">Voeg een abonnement toe:</label>
						<select name="abo" id="addabo_abo">
							{foreach from=$maal.abo.nietAbos key=abosoort item=abotekst}
								<option value="{$abosoort}">{$abotekst}</option>
							{/foreach}
						</select>
						<input type="submit" name="fuh" value="toevoegen" />
					</form>
				{/if}
			</td>
		</tr>
	</table>
</p>
<br />
