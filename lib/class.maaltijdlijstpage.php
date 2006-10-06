<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.maaltijdlijstpage.php
# -------------------------------------------------------------------
#
# Weergeven van de te printen maaltijdlijst voor een bepaalde
# maaltijd.
#
# -------------------------------------------------------------------
# Historie:
# 26-02-2006 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdLijstPage extends SimpleHTML {
	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltijd;

	### public ###

	function MaaltijdLijstPage (&$lid, &$maaltijd) {
		$this->_lid =& $lid;
		$this->_maaltijd =& $maaltijd;
	}

	# we gaan een lijst met maaltijdaanmeldingen printen. de uitvoer is gewoon een html
	# pagina die de koks in de browser kunnen uitprinten.
	# we vragen de lijst met aanmeldingen op bij de maaltijd, alsmede gegevens over de tp, datum,
	# etc...
	function view() {
	
		$aanmeldingen = $this->_maaltijd->getAanmeldingen_Oud();
		
		$datumtekst = strftime('%A %e %B %Y', $this->_maaltijd->getDatum());
		$tptekst = $this->_lid->getCivitasName($this->_maaltijd->getTP());
		
		# aantal aanmeldingen gebruiken we als basis voor wat andere variabelen
		$aantal = count($aanmeldingen);
		
		# zoals kolommenindeling
		$helft = ceil( ($aantal) / 2);
		# zoals kostenberekening
		$marge = 4;
		$eters = $aantal + $marge;
		$euristekst = sprintf("%01.2f" ,$eters * 1.70);
		
		# info voor lege velden in de lijst
		$leegveld = array(
			'naam' => '',
			'eetwens' => ''
		);
		
		print(<<<EOT
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>

<html>
<head>
  <title>Maaltijdaanmeldingen van {$datumtekst}</title>
  <style type="text/css">
		body{
			font-family: verdana;
			font-size: 10px;
		}
		a{
			text-decoration: none;
			border: 0px;
		}
		img{
		
		}
		table.hoofdtabel{
			border: 0px;
			width: 100%;
		}
		table.inschrijvingen{
			border-collapse: collapse;
			border: 1px solid black;
			width: 100%;
		}
		table.inschrijvingen TD{
		 	border: 1px solid black;
		 	padding: 3px 5px 2px 10px;
		 	vertical-align: top;
		}
		table.inschrijvingen TD.nummer{
			width: 20px;
			
		}
		table.inschrijvingen TD.vink-vakje{
			width: 40px;
		}
		
		table.overzicht{
			border-collapse: collapse;
			border: 0px;
			width: 100%;
		}
		table.overzicht TD{
			width: 40%;
			vertical-align: top;
					 	padding: 3px 5px 2px 10px;
		}
		table.overzicht TD.overzicht{
			
			border-right: thin solid black;
		} 
		table.overzicht TD.corvee{
			border-left: thin solid black;
		} 
		
	</style>
</head>
<body>
<table class="hoofdtabel">
<tr><td>
<h1>C.S.R.-maaltijd {$datumtekst}</h1>
Regels omtrent het betalen van de maaltijden op Confide:
<ul>
<li>maaltijdprijs: &euro; 2,50</li>
<li>niet betaald = nb</li>
<li>2,50 betaald = kruisje (x)</li>
<li>ander bedrag ingelegd: schrijf duidelijk in het hokje hoeveel je in de helm hebt gegooid.</li>
<li> als je géén tegoed hebt bij de maalcie betekent een niet direct betaalde maaltijd 20 cent boete!</li>
</ul>
Tafelpraeses is vandaag {$tptekst}<br /><br />
</td></tr>
EOT
		);


		# Als de maaltijd nog niet gesloten is, beelden we een linkje af.
		if (!$this->_maaltijd->isGesloten()) {

			$maalid = $this->_maaltijd->getMaalId();

			print(<<<EOT
<tr><td>
<h1><font color="red">De inschrijving voor deze maaltijd is nog niet gesloten</h1>
<a href="{$_SERVER['PHP_SELF']}?maalid={$maalid}&sluit=1" onclick="return confirm('Weet u zeker dat u deze maaltijd wil sluiten?')">Nu sluiten! (N.B. Dit is een onomkeerbare stap!!)</a><br /><br />
</td></tr>
EOT
			);

		}

		print(<<<EOT
<tr><td>
<table class="inschrijvingen">
EOT
		);

		if ($aantal > 0) {

			reset($aanmeldingen); $i = 0;
			$aanmelding = current($aanmeldingen);
			do {

				# linkerveld
				$i++;
				print("<tr>");
				printf('<td class="nummer">%s</td><td>%s',$i,mb_htmlentities($aanmelding['naam']));
				if ($aanmelding['eetwens'] != '') print('<br /><b>' . mb_htmlentities(trim($aanmelding['eetwens'])) . '</b>');
				print('</td><td class="vink-vakje">&nbsp;</td>');
				
				# rechterveld
				# JAAA in de volgende regel staat een = en NIET een == !!!
				if (!$aanmelding = next($aanmeldingen)) {
					$aanmelding = $leegveld;
					$j = '&nbsp;';
				} else $j = ($i+$helft);
				printf('<td class="nummer">%s</td><td>%s',$j,mb_htmlentities($aanmelding['naam']));
				if ($aanmelding['eetwens'] != '') print('<br /><b>' . mb_htmlentities(trim($aanmelding['eetwens'])) . '</b>');
				print('</td><td class="vink-vakje">&nbsp;</td>');
				print("</tr>\n");
			} while ($aanmelding = next($aanmeldingen));
		}

		print(<<<EOT

<tr><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td></tr>
<tr><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td></tr>
<tr><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td></tr>
<tr><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td></tr>
<tr><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td><td class="nummer">..</td><td>..</td><td class="vink-vakje">..</td></tr>

</table>
</td></tr><tr><td>&nbsp;</td></tr>
<tr><td>
<table class="overzicht">
	<tr>
		<td class="overzicht">
			<strong>Overzicht</strong><br />
			<table border="0" style="width: 100%">
				<tr><td>Aantal inschrijvingen</td><td>{$aantal}</td></tr>
				<tr><td>Marge i.v.m. gasten</td><td>{$marge}</td></tr>
				<tr><td>Eters</td><td>{$eters}</td></tr>
				<tr><td>Budget koks</td><td>&euro; {$euristekst}</td></tr>
			</table>	
		</td>
		<td class="corvee">
			<strong>Corvee</strong><br />
			<table border="0" style="width: 100%">
				<tr><td>koks:</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
				<tr><td>afwassers:</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
				<tr><td>&nbsp;</td><td>...</td></tr>
		</td></tr>
</table>
</td></tr>

</table>

</body>
</html>
EOT
		);
	}
	
}

?>
