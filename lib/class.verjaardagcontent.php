<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.verjaardagcontent.php
# -------------------------------------------------------------------
# Beeldt informatie af over Verjaardagen
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');

class VerjaardagContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_actie;

	### public ###

	function VerjaardagContent (&$lid, $actie) {
		$this->_lid =& $lid;
		$this->_actie = $actie;
		
	}
	function getTitel(){
		return 'Verjaardagen';
	}
	function view() {
		switch ($this->_actie) {
			case 'alleverjaardagen':
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
		
				if(!isset($_GET['print'])){
					echo '<a href="verjaardagen.php?print=true">printversie</a>'."\n";
				}
				echo '<table style="width: 100%;">';
				for ($r=0; $r<$rijen; $r++) {
					echo '<tr>';
					for ($k=1; $k<=$kolommen; $k++) {
						$maand = ($r*$kolommen+$k+$dezemaand-2)%12+1;
						$tekst = ($maand <= 12) ? $maanden[$maand] : '&nbsp;';
						echo '<th>'.$tekst.'</th>'."\n";
					}
					echo "</tr><tr>\n";
					for ($k=1; $k<=$kolommen; $k++) {
						$maand = ($r*$kolommen+$k+$dezemaand-2)%12+1;
						if ($maand <= 12) {
							echo '<td>'."\n";
							$vrjdgn = $this->_lid->getVerjaardagen($maand);
							foreach ($vrjdgn as $vrjdg){
								if ($vrjdg['gebdag'] == $dezedag and $maand == $dezemaand) echo '<span class="waarschuwing">';
								echo $vrjdg['gebdag'] . " ";
								echo mb_htmlentities(naam($vrjdg['voornaam'], $vrjdg['achternaam'],$vrjdg['tussenvoegsel']))."<br />\n";
								if ($vrjdg['gebdag'] == $dezedag and $maand == $dezemaand) echo "</span>";
							}
							echo "<br /></td>\n";
						} else {
							echo "<td><&nbsp;</td>\n";
						}
					}
					echo "</tr>\n";
				}
				echo '</table><br>'."\n";
				break;
				
			case 'komende10':
				$aVerjaardagen=$this->_lid->getKomende10Verjaardagen();
				echo '<div id="komendeVerjaardagen"><a href="/intern/verjaardagen.php" class="kopje">Komende verjaardagen:</a><br />';
				for ($i=0; $i<sizeOf($aVerjaardagen); $i++) {					
					$aVerjaardag = $aVerjaardagen[$i];

					if ($i == 0 || $aVerjaardag['jarig_over'] != $aVerjaardagen[$i-1]['jarig_over']) {
						$tekst = '<i>';
						switch ($aVerjaardag['jarig_over']) {
							case 0:
								$tekst .= 'Vandaag:';
							break;
								
							case 1:
								$tekst .= 'Morgen:';
							break;
							
							default:
								$tekst .= 'Over ' . $aVerjaardag['jarig_over'] . ' dagen:';
						}
						$tekst.='</i><br />';
					}
					
					$tekst.=$this->_lid->getNaamLink($aVerjaardag['uid'], 'civitas', true, $aVerjaardag);
					$tekst .= ' (<b>' . $aVerjaardag['leeftijd'] . '</b>)<br />';
					
					echo $tekst;
					$tekst = '';
				}
				echo '</div>';
				break;
		}
	}
}

?>
