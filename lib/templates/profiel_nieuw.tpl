<div id="profiel">
	<div id="profielregel">
		<div class="naam">
			<div class="floatR">{$profhtml.foto}</div>
			<h1>{$profhtml.fullname}</h1>
		</div>
	</div>
	
	<div class="profielregel">
		<div class="left">Naam</div>
		<div class="gegevens">
			<div class="label">&nbsp;</div>{$profhtml.civitasnaam}<br />
			<div class="label">Lidnummer:</div> {$profhtml.uid} <br />
			<div class="label">Bijnaam:</div> {$profhtml.nickname} <br />
			{if $profhtml.gebdatum!='0000-00-00'}<div class="label">Geb.datum:</div> {$profhtml.gebdatum|date_format:"%d-%m-%Y"}{/if}
		</div>
	</div>
	<div class="profielregel">
		<div class="left">Adres</div>	
		<div class="gegevens">
			<div class="gegevenszelf">
				<div class="label">
					{if $profhtml.adres!=''}
						<a href="http://maps.google.nl/maps?daddr={$profhtml.adres|urlencode}+{$profhtml.woonplaats|urlencode}+{$profhtml.land|urlencode}">
							<img src="{$csr_pics}layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
						</a>
					{/if}
				</div>
				<div class="adres">
					{$profhtml.woonoord}
					{$profhtml.adres}<br />
					{$profhtml.postcode} {$profhtml.woonplaats}<br />
					{$profhtml.land}<br />
					{if $profhtml.telefoon!=''}{$profhtml.telefoon}<br />{/if}
					{if $profhtml.mobiel!=''}{$profhtml.mobiel}<br />{/if}
				</div>
			</div>
			<div class="gegevensouders">			
				{if $profhtml.o_adres!=''}
					<div class="label">
						<a href="http://maps.google.nl/maps?daddr={$profhtml.o_adres|urlencode}+{$profhtml.o_woonplaats|urlencode}+{$profhtml.o_land|urlencode}">
							<img src="{$csr_pics}layout/googlemaps.gif" width="35px" alt="googlemap voor dit adres" />
						</a>
					</div>
				{/if}
				<div class="adres">
					{if $profhtml.o_adres!=''}
						<strong>Ouders:</strong><br />
						{$profhtml.o_adres}<br />
						{$profhtml.o_postcode} {$profhtml.o_woonplaats}<br />
						{$profhtml.o_land}<br />
						{$profhtml.o_telefoon}
					{/if}
				</div>
			</div>
			<div style="clear: left;"></div>
		</div>
	</div>
	<div class="profielregel">
		<div class="left">Contact</div>	
		<div class="gegevens">
			<div class="label">Email:</div>{$profhtml.email}<br />	
			{if $profhtml.icq!=''}<div class="label">ICQ:</div> {$profhtml.icq}<br />{/if}
			{if $profhtml.msn!=''}<div class="label">MSN:</div> {$profhtml.msn}<br />{/if}
			{if $profhtml.jid!=''}<div class="label">Jabber:</div> {$profhtml.jid}<br />{/if}
			{if $profhtml.skype!=''}<div class="label">Skype:</div> {$profhtml.skype}<br />{/if}
			{if $profhtml.website!=''}<div class="label">Website:</div> <a href="{$profhtml.website}" class="linkExt">{$profhtml.website|truncate:30}</a><br />{/if}
		</div>	
	</div>
	<div class="profielregel">
		<div class="left">Civitas</div>	
		<div class="gegevens">
			<div class="label">Studie:</div> {$profhtml.studie}<br />
			<div class="label">Studie sinds:</div> {$profhtml.studiejaar}<br />
			<div class="label">Lid sinds:</div> {$profhtml.lidjaar}<br />
			<br />
			{if $isOudlid!==true}
				<div class="label">Kring:</div> <a href="/communicatie/moten.php">{$profhtml.moot}.{$profhtml.kring}</a><br />
			{/if}
		</div>
	</div>
	<div class="profielregel">
		<div class="left">Groepen</div>	
		<div class="gegevens">		
			{$profhtml.groepen}
			<div style="clear: left;"></div>
		</div>
	</div>
	<div class="profielregel">
		<div class="left">Financi&euml;el</div>	
		<div class="gegevens">		
			{if $profhtml.bankrekening!=''}<div class="label">Bankrekening:</div> {$profhtml.bankrekening}<br />{/if}
			{$profhtml.saldografiek}
		</div>
	</div>
	
	<div class="profielregel">
		<div class="left">Maaltijden</div>	
		<div class="gegevens">		
			{if $profhtml.abos|@count > 0}
				<div class="label">Abo's</div>
				<ul class="nobullets data">
				{foreach from=$profhtml.abos item=abo}
					<li>{$abo}</li>
				{/foreach}
				</ul>
			{/if}
			<div class="label">Recent</div>
			<div class="data">
			
			</div>
		</div>
	</div>
	
</div>