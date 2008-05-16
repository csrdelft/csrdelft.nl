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

	function VerjaardagContent ($actie) {
		$this->_lid =Lid::get_lid();
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
							$verjaardagen = $this->_lid->getVerjaardagen($maand);
							foreach ($verjaardagen as $verjaardag){
								if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand) echo '<em>';
								echo $verjaardag['gebdag'] . " ";
								echo $this->_lid->getNaamLink($verjaardag['uid'], 'full', false, $verjaardag)."<br />\n";
								if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand) echo "</em>";
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
				echo '<h1><a href="/communicatie/verjaardagen.php">Verjaardagen</a></h1>';
				for ($i=0; $i<sizeOf($aVerjaardagen); $i++) {					
					$aVerjaardag = $aVerjaardagen[$i];

					echo '<div class="item">'.date('d-m', strtotime($aVerjaardag['gebdatum'])).' ';
					if($aVerjaardag['jarig_over']==0){echo '<em>';}
					echo $this->_lid->getNaamLink($aVerjaardag['uid'], 'civitas', true, $aVerjaardag);
					if($aVerjaardag['jarig_over']==0){echo '</em>';}
					echo '</div>';
				}
				break;
		}
	}
}

?>
