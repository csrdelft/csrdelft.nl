<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.maaltijdcontent.php
# -------------------------------------------------------------------
#
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
#
# -------------------------------------------------------------------
# Historie:
# 20-01-2006 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltrack;

	### public ###

	function MaaltijdContent (&$lid, &$maaltrack) {
		$this->_lid =& $lid;
		$this->_maaltrack =& $maaltrack;
	}

	function view() {
		# is er een foutboodschap?
		$error = $this->_maaltrack->getError();
		if ($error != '') $errortxt = "<span class=\"bodyrood\">N.B.: " . htmlentities($error) . "</span><br /><br />\n";
		else $errortxt = '';
	
		# introducerende tekst
		print(<<<EOT

<!--<span class="bodyrood">N.B.! Deze maaltijdaanmelding is nog in test-fase en de inschrijvingen hier worden
nog NIET gebruikt door de koks. Gebruik de maaltijdinschrijving op de oude website als u zich voor een
maaltijd wil inschrijven. In de week direct na de Diesweek zal deze inschrijvingketzer actief worden.</span>
<br /><br />-->

<table width="100%" class="lijnhoktable">
<tr><td width="100%" class="lijnhoktitel">Maaltijden</td></tr>
<tr><td width="100%" class="lijnhoktekst">

Op deze pagina kunt u zich inschrijven voor maaltijden op Confide. Onderstaande tabel toont de maaltijden in de
komende weken. Onder "Kom ik eten?" ziet u de huidige status van uw inschrijving voor de maaltijd.<br />
<br />
U kunt uw inschrijving wijzigen door gebruik te maken van de opties die aan het einde van elke regel staan.<br />
N.B. De maaltijdinschrijving sluit op de dag van de maaltijd rond 15:00, als de koks de lijst met aanmeldingen
uitprinten. Vanaf dat moment zal deze ketzer u niet meer willen aan- of afmelden!<br />
<br />
Prefereert u vegetarisch eten, of heeft u speciale eetgewoontes of een dieet, gebruik dan het vakje 'Eetwens' in uw
profielinstellingen om dat aan te geven.<br />
<br />

{$errortxt}
EOT
		);
		
		# haal maaltijden op
		$van = time(); # vanaf nu
		$tot = $van + MAALTIJD_LIJST_MAX_TOT; # zie include.config.php
		$maaltijden = $this->_maaltrack->getMaaltijden($van, $tot);

		if (count($maaltijden) > 0) {
		
			print(<<<EOT

<table cellpadding="0" cellspacing="5" marginwidth="0" marginheight="0" border="0" align="left" width="100%">

<tr>
<td valign="top"><b>Maaltijd begint om:</b></td>
<td valign="top"><b>Menu</b></td>
<td valign="top"><b>Aantal(Max)</b></td>
<td valign="top"><b>Kom ik eten?</b></td>
<td valign="top"><b>Wijzig in:</b></td>
</tr>

EOT
			);
		
		
			# laat de maaltijden zien
			foreach ($maaltijden as $m) {
		
				# kleurtjes voor abo's
				if ($m['status'] == 'AAN') $m['status'] = "<font color=green><b>JA!</b></font>";
				elseif ($m['status'] == 'ABO') $m['status'] = "<font color=green><b>JA! (Abo)</b></font>";
				elseif ($m['status'] == 'AF') {
					#$watbenjedan = array('ranzig', 'een feut', 'zielig', 'kansloos', 'een drukfeut', 'suf en traag', 'randlid', 'zuur', "bij moeders eten", 'karig');
					#$welke = rand(0,count($watbenjedan)-1);
					#$m['status'] = "<font color=red><b>NEE, ik ben {$watbenjedan[$welke]}!</b></font>";
					$m['status'] = "<font color=red><b>NEE</b></font>";
				} else $m['status'] = '<b>NEE</b>';
				# link voor acties
				if ($m['gesloten'] == '1') $m['actie'] = "Inschrijving Gesloten";
				elseif ($m['actie'] == 'aan')
					$m['actie'] = "<a href=\"{$_SERVER['PHP_SELF']}?a=aan&m={$m['id']}\">[ ik kom WEL! ]</a>";
				elseif ($m['actie'] == 'af')
					$m['actie'] = "<a href=\"{$_SERVER['PHP_SELF']}?a=af&m={$m['id']}\">[ ik kom NIET! ]</a>";
				# aantal(max) of VOL
				if ($m['aantal'] < $m['max']) $aantalmax = $m['aantal'] . " (" . $m['max'] . ")";
				else $aantalmax = "VOL (" . $m['max'] . ")";
				# datum formatteren
				$m['datum'] = strftime('%a %e %b %H:%M', $m['datum']);
				# tekst eksaepen
				$m['tekst'] = htmlentities($m['tekst']);

				print(<<<EOT
<tr>
<td valign="top">{$m['datum']}</td>
<td valign="top">{$m['tekst']}</td>
<td valign="top">{$aantalmax}</td>
<td valign="top">{$m['status']}</td>
<td valign="top">{$m['actie']}</td>
</tr>
EOT
				);		
			}

			print("</table>\n");
		} else {
			print("&#8226; Helaas, er is binnenkort geen maaltijd op Confide.<br /><br />\n");		
		}

		print(<<<EOT


</td></tr>

<tr><td width="100%" class="lijnhoktitel">Maaltijdabonnementen</td></tr>
<tr><td width="100%" class="lijnhoktekst">

<table cellpadding="0" cellspacing="5" marginwidth="0" marginheight="0" border="0" align="left" width="100%">
<tr><td>
EOT
);
		# haal de abos op die deze gebruiker heeft
		$abos = $this->_maaltrack->getAbo();
		if (count($abos) > 0) {
			print("<table cellpadding=\"0\" cellspacing=\"5\" marginwidth=\"0\" marginheight=\"0\" border=\"0\" align=\"left\" width=\"300\"\n");
			foreach ($abos as $abosoort => $tekst) {
				printf(
					"<tr><td valign=\"top\">&#8226; %s </td><td valign=\"top\"><a href=\"%s?a=delabo&abo=%s\">[ uitschakelen ]</td></tr>\n",
					$tekst,
					$_SERVER['PHP_SELF'],
					$abosoort
				);
			}
			print("</table>\n");
		} else {
			print("<table><tr><td>&#8226;</td><td>Er is geen maaltijdabonnement geactiveerd.</td></tr></table>\n");
		}

		print("<br clear=\"all\" />\n");

		# kijk of er abo's zijn die de gebruiker nog meer aan kan zetten
		$geenabo = $this->_maaltrack->getNotAboSoort();

		if (count($geenabo) > 0) {

			print(<<<EOT
			
</td><td>			
			
<form action="{$_SERVER['PHP_SELF']}" method="POST">
<input type="hidden" name="a" value="addabo">
Voeg een abonnement toe:
<select name="abo" class="tekst">
EOT
			);

			foreach ($geenabo as $abosoort => $tekst) printf("<option value=\"%s\">%s</option>\n", $abosoort, $tekst);

			print(<<<EOT
</select>
<input type="submit" class="tekst" name="fuh" value=" toevoegen ">
</form>

<!--N.B. Als u een abonnement inschakelt, kan het gebeuren, dat maaltijden waarop uw abo van toepassing is reeds VOL zijn.
In dat geval wordt u NIET aangemeld voor die betreffende maaltijd(en), maar juist expliciet AF. Als u het abonnement
daarna weer uitschakelt worden deze afmeldingen NIET automatisch weer gewist.<br />-->

EOT
			);

		}


		print(<<<EOT

</td></tr></table>
<br clear="all" />
EOT
		);
		
		# Anderen aanmelden voor een maaltijd

		# is er een foutboodschap?
		$error = $this->_maaltrack->getProxyError();
		if ($error != '') $errortxt = "<span class=\"bodyrood\">" . htmlentities($error) . "</span><br /><br />\n";
		else $errortxt = '';

		print(<<<EOT

</td></tr>

<tr><td width="100%" class="lijnhoktitel">Andere verenigingsleden aanmelden</td></tr>
<tr><td width="100%" class="lijnhoktekst">

Het is voor leden alleen mogelijk andere leden aan te melden binnen 48 uur voordat de maaltijd plaatsvindt.
U kunt iemand aanmelden met zijn/haar 4-cijferige lid-nummer. Als iemand u vraagt hem/haar in te schrijven,
vraag hier dan even naar, of zoek het op in de ledenlijst.<br />
<br />
{$errortxt}
EOT
		);

		# haal maaltijd op die binnen 48 uur is
		$van = time(); # vanaf nu
		$tot = $van + (MAALTIJD_PROXY_MAX_TOT);
		$maaltijden = $this->_maaltrack->getMaaltijden($van, $tot);
	
		if (count($maaltijden) > 0) {
			print(<<<EOT
		
<table cellpadding="0" cellspacing="5" marginwidth="0" marginheight="0" border="0" width="100%" align="left">
<tr>
<td witdh="140"><b>Maaltijd:</b></td>
<td width="140"><b>Lid-nummer:</b></td>
<td width="100">&nbsp;</td>
<td><b>U heeft naast uzelf ook aangemeld:</b></td>
</tr>
EOT
			);
			foreach ($maaltijden as $m) {
				# datum formatteren
				$m['datum'] = strftime('%a %e %b %H:%M', $m['datum']);
				# begin een form met de tekst en een invulvak en een knop
				# maar alleen als de inschrijving nog niet gesloten is
				if ($m['gesloten'] != '1')	print(<<<EOT
<tr>
<td valign="top">
<form action="{$_SERVER['PHP_SELF']}" method="POST">
<input type="hidden" name="a" value="aan">
<input type="hidden" name="m" value="{$m['id']}">
{$m['datum']}
</td>
<td valign="top"><input type="text" name="uid" class="tekst" style="width:140px;" value=""></td>
<td valign="top">
<input type="image" src="/images/aanmelden.gif" width="81" height="12" alt="aanmelden" name="foo" value="bar">
</form>
</td><td valign="top">
EOT
					);
				else print(<<<EOT
<tr>
<td valign="top">{$m['datum']}</td>
<td valign="top" colspan="2">Inschrijving gesloten</td>
<td>
EOT
					);					
					
				$wienogmeer = $this->_maaltrack->getProxyAanmeldingen($this->_lid->getUid(), $m['id']);
				if (count($wienogmeer) > 0) {
					print(<<<EOT
	<table cellpadding="0" cellspacing="0" marginwidth="0" marginheight="0" border="0" align="left" width="100%">
EOT
					);
					foreach($wienogmeer as $wie => $naam) {
						print(<<<EOT
	<tr>
	<td>{$naam}</td>
	<td><a href="{$_SERVER['PHP_SELF']}?a=af&m={$m['id']}&uid={$wie}">[ afmelden ]</a></td>
	</tr>
EOT
						);
					}
					print(<<<EOT
	</table>
EOT
					);
					#print(implode('; ',$wienogmeer));
				} else print("-");
				print(<<<EOT
</td>
</tr>
EOT
				);
			}
			print("</table>\n");
		} else {
			print("&#8226; Helaas, de komende 48 uur is er geen maaltijd op Confide.<br /><br />\n");		
		}
		
		print(<<<EOT

</td></tr>

<tr><td width="100%" class="lijnhoktitel">Gasten aanmelden</td></tr>
<tr><td width="100%" class="lijnhoktekst">
<br />
U kunt op uw naam gasten aanmelden voor de maaltijd.<br />
Dit onderdeel is nog niet afgerond helaas.<br />
Gasten kunt u opgeven door een e-post berichtje te sturen naar maaltijden@csrdelft.nl<br />
<br />
</td></tr>
</table>
EOT
		);

	}
}

?>
