<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.motencontent.php
# -------------------------------------------------------------------
#
# Beeldt de moot/kringindeling af
#
# -------------------------------------------------------------------
# Historie:
# 13-09-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');

class MotenContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;

	### public ###

	function MotenContent (&$lid) {
		$this->_lid =& $lid;
	}

	function view() {
		$kring = $this->_lid->getKringen();
		
		# we willen weten hoeveel moten en wat het max aantal kringen in een moot is...
		$maxmoten = $this->_lid->getMaxMoten();
		$maxkringen = $this->_lid->getMaxKringen();

		print(<<<EOT
<center><span class="kopje2">Moot en Kringindeling</span></center><p>

<table width="100%" class="lijnhoktable" border="1" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
EOT
		);

		# we gaan de kringen in de moot onder elkaar zetten, een moot per kolom
		for ($r=1; $r<=$maxkringen; $r++) {
			print ("<tr>\n");
			for ($k=1; $k<=$maxmoten; $k++) {
				if (isset($kring[$k][$r])) print ("<td class=\"lijnhoktitel\">Kring {$k}.{$r}</td>\n");
				else print("<td class=\"lijnhoktitel\">&nbsp;</td>\n");
			}
			print ("</tr><tr>\n");
			for ($k=1; $k<=$maxmoten; $k++) {
				if (isset($kring[$k][$r])) {
					print("<td class=\"lijnhoktekst\">");
					foreach ($kring[$k][$r] as $kringlid) {
						if ($kringlid['kringleider'] != 'n') echo "<span class=\"tekstrood\">";
						if ($kringlid['motebal']!=0) echo '<span class="tekstblauw">';
						echo htmlentities($kringlid['naam']);
						if ($kringlid['motebal']!='0') echo '&nbsp;O';
						if ($kringlid['status']=='S_KRINGEL') echo '&nbsp;~';
						echo "<br />\n";
						if ($kringlid['kringleider'] != 'n' OR $kringlid['motebal']!=0) echo "</span>";
					}
					print ("</td>\n");
				} else print("<td class=\"lijnhoktekst\">&nbsp;</td>\n");
			}
		}

		# nu nog even de kringlozen
		$r = 0;
		print ("<tr>\n");
		for ($k=1; $k<=$maxmoten; $k++) {
			if (isset($kring[$k][$r])) print ("<td class=\"lijnhoktitel\">Kring {$k}.{$r}</td>\n");
			else print("<td class=\"lijnhoktitel\">&nbsp;</td>\n");
		}

		print ("</tr><tr>\n");

		for ($k=1; $k<=$maxmoten; $k++) {
			if (isset($kring[$k][$r])) {
				print("<td class=\"lijnhoktekst\">");
				foreach ($kring[$k][$r] as $kringlid) {
					if ($kringlid['kringleider'] != 'n') echo "<span class=\"tekstrood\">";
					echo htmlentities($kringlid['naam']);
					if ($kringlid['kringleider'] != 'n') echo "</span>";
					echo "<br />\n";
				}
				print ("</td>\n");
			} else print("<td class=\"lijnhoktekst\">&nbsp;</td>\n");
		}

		print(<<<EOT
</tr>
</table>
<br clear="all">
EOT
		);
	}
}

?>
