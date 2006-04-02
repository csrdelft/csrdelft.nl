<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.verjaardagcontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Verjaardagen
#
# -------------------------------------------------------------------
# Historie:
# 07-09-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');

class VerjaardagContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;

	### public ###

	function VerjaardagContent (&$lid) {
		$this->_lid =& $lid;
	}

	function view() {
		# de verjaardagen die vandaag zijn krijgen een highlight
		$nu = time();
		$dezemaand = date('n', $nu);
		$dezedag = date('j', $nu);
		
		# afbeelden van alle verjaardagen in 3 rijen en 4 kolommen
		$rijen = 3; $kolommen = 4;

		$maanden = array (
			1 => "Januari",
			2 => "Februari",
			3 => "Maart",
			4 => "April",
			5 => "Mei",
			6 => "Juni",
			7 => "Juli",
			8 => "Augustus",
			9 => "September",
			10 => "Oktober",
			11 => "November",
			12 => "December",
		);

		print('<center><span class="kopje2">Verjaardagskalender</span></center><p>'."\n");
		print('<table width="100%" class="lijnhoktable" border="1" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">'."\n");
		for ($r=0; $r<$rijen; $r++) {
			print("<tr>\n");
			for ($k=1; $k<=$kolommen; $k++) {
				$maand = ($r*$kolommen+$k+$dezemaand-2)%12+1;
				$tekst = ($maand <= 12) ? $maanden[$maand] : '&nbsp;';
				print("<td class=\"lijnhoktitel\">{$tekst}</td>\n");
			}
			print("</tr><tr>\n");
			for ($k=1; $k<=$kolommen; $k++) {
				$maand = ($r*$kolommen+$k+$dezemaand-2)%12+1;
				if ($maand <= 12) {
					print('<td class="lijnhoktekst">'."\n");
					$vrjdgn = $this->_lid->getVerjaardagen($maand);
					foreach ($vrjdgn as $vrjdg) {
						if ($vrjdg['gebdag'] == $dezedag and $maand == $dezemaand) echo '<span class="tekstrood">';
						echo $vrjdg['gebdag'] . " ";
						echo mb_htmlentities($vrjdg['voornaam']);
						if ($vrjdg['tussenvoegsel'] != "") echo " ".mb_htmlentities($vrjdg['tussenvoegsel']);
						echo " ".mb_htmlentities($vrjdg['achternaam']) . "<br />\n";
						if ($vrjdg['gebdag'] == $dezedag and $maand == $dezemaand) echo "</span>";
					}
					print("</td>\n");
				} else {
					print("<td><&nbsp;</td>\n");
				}
			}
			print("</tr>\n");
		}
		print('</table><br clear="all">'."\n");
	}
}

?>
