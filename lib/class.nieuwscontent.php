<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.nieuwscontent.php
# -------------------------------------------------------------------
#
# Beeldt de berichten af die in een Nieuws-object zitten.
#
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.nieuws.php');

class NieuwsContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_nieuws;

	# afbreken van de tekst van een berichtje bij de eerste spatie
	# voor het $chop-de karakter, 0 = niet gebruiken
	var $_chop = 0;

	### public ###

	function NieuwsContent (&$nieuws) {
		$this->_nieuws =& $nieuws;
	}

	function setChop($chars) { $this->_chop = (int)$chars; }

	function view() {
?>
<center><span class="kopje2">Nieuws</span></center>
<?php
		$messages = $this->_nieuws->getMessages();
		if (sizeof($messages) == 0) {	
			echo 'Zoals het is, zoals het was, o Civitas!<br />(Geen nieuws gevonden dus....)';
		} else {
			foreach ($messages as $message) {
				if ($this->_chop != 0 and strlen($message['tekst']) > $this->_chop) {
					# eerst afhakken alles na chop
					$message['tekst'] = substr($message['tekst'], 0, $this->_chop);
					# zoek laatste spatie die er nog in zit, vanaf PHP 5 kan
					# je pas op een offset beginnen...
					$lastspace = strrpos($message['tekst'], ' ');
					# als die er is tot daar afhakken
					if ($lastspace) $message['tekst'] = substr($message['tekst'], 0, $lastspace) . "... ([url={$_SERVER['PHP_SELF']}?id={$message['id']}]lees verder[/url])";
				}
			echo '<span class="kopje3">'.htmlentities($message['titel']).'</span>
					<i>('.date('d-m-Y H:i:s', $message['datum']).')</i><br />
					'.bbview($message['tekst'], $message['bbcode_uid']).'<br clear="all"><br />';
			}
		}

	}
}

?>
