<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.verjaardagcontent.php
# -------------------------------------------------------------------
# Beeldt informatie af over Verjaardagen
# -------------------------------------------------------------------

require_once 'lid/verjaardag.class.php';

class VerjaardagContent implements View {

	private $model;

	public function __construct($verjaardagen) {
		$this->model = $verjaardagen;
	}

	function getTitel() {
		return 'Verjaardagen';
	}

	function getModel() {
		return $this->model;
	}

	function view() {
		switch ($this->model) {
			case 'alleverjaardagen':
				echo '<ul class="horizontal nobullets">
						<li>
							<a href="/communicatie/ledenlijst/">Ledenlijst</a>
						</li>
						<li class="active">
							<a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
						</li>
						<li>
							<a href="/communicatie/verticalen/">Kringen</a>
						</li>
					</ul>
					<hr />';
				echo '<h1>Verjaardagen</h1>';
				# de verjaardagen die vandaag zijn krijgen een highlight
				$nu = time();
				$dezemaand = date('n', $nu);
				$dezedag = date('j', $nu);

				# afbeelden van alle verjaardagen in 3 rijen en 4 kolommen
				$rijen = 3;
				$kolommen = 4;

				$maanden = array(
					1	 => "Januari",
					2	 => "Februari",
					3	 => "Maart",
					4	 => "April",
					5	 => "Mei",
					6	 => "Juni",
					7	 => "Juli",
					8	 => "Augustus",
					9	 => "September",
					10	 => "Oktober",
					11	 => "November",
					12	 => "December",
				);

				echo '<table style="width: 100%;">';
				for ($r = 0; $r < $rijen; $r++) {
					echo '<tr>';
					for ($k = 1; $k <= $kolommen; $k++) {
						$maand = ($r * $kolommen + $k + $dezemaand - 2) % 12 + 1;
						$tekst = ($maand <= 12) ? $maanden[$maand] : '&nbsp;';
						echo '<th>' . $tekst . '</th>' . "\n";
					}
					echo "</tr><tr>\n";
					for ($k = 1; $k <= $kolommen; $k++) {
						$maand = ($r * $kolommen + $k + $dezemaand - 2) % 12 + 1;
						if ($maand <= 12) {
							echo '<td>' . "\n";
							$verjaardagen = Verjaardag::getVerjaardagen($maand);
							foreach ($verjaardagen as $verjaardag) {
								$lid = LidCache::getLid($verjaardag['uid']);
								if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand)
									echo '<em>';
								echo $verjaardag['gebdag'] . " ";
								echo $lid->getNaamLink('civitas', 'visitekaartje') . "<br />\n";
								if ($verjaardag['gebdag'] == $dezedag and $maand == $dezemaand)
									echo "</em>";
							}
							echo "<br /></td>\n";
						} else {
							echo "<td><&nbsp;</td>\n";
						}
					}
					echo "</tr>\n";
				}
				echo '</table><br>' . "\n";
				break;
			case 'komende':
				if (LoginModel::getUid() == 'x999') {
					$toonpasfotos = false;
				} else {
					$toonpasfotos = LidInstellingen::get('zijbalk', 'verjaardagen_pasfotos') == 'ja';
				}

				echo '<div id="zijbalk_verjaardagen"><h1>';
				if (LoginModel::mag('P_LEDEN_READ')) {
					echo '<a href="/communicatie/verjaardagen">Verjaardagen</a>';
				} else {
					echo 'Verjaardagen';
				}
				echo '</h1>';

				$aantal = LidInstellingen::get('zijbalk', 'verjaardagen');
				if ($toonpasfotos) {
					//veelvouden van 3 overhouden
					$aantal = $aantal - ($aantal % 3);
					if ($aantal < 3) {
						$aantal = 3;
					}
				}
				//verjaardagen opvragen voor 30 dagen vooruit, met een limiet als hierboven 
				//gedefenieerd.
				$aVerjaardagen = Lid::getVerjaardagen(time(), time() + 3600 * 24 * 30, $aantal);

				if ($toonpasfotos) {
					echo '<div class="item" id="komende_pasfotos">';
					foreach ($aVerjaardagen as $lid) {
						echo '<div class="verjaardag';
						if ($lid->isJarig()) {
							echo ' opvallend';
						}
						echo '">';
						echo $lid->getNaamLink('pasfoto', 'link');
						echo '<span class="datum">' . date('d-m', strtotime($lid->getGeboortedatum())) . '</span>';
						echo '</div>';
					}
					echo '<div class="clear"></div></div>';
				} else {
					foreach ($aVerjaardagen as $lid) {
						echo '<div class="item">' . date('d-m', strtotime($lid->getGeboortedatum())) . ' ';
						if ($lid->isJarig()) {
							echo '<span class="opvallend">';
						}
						echo $lid->getNaamLink('civitas', 'visitekaartje');
						if ($lid->isJarig()) {
							echo '</span>';
						}
						echo '</div>';
					}
				}
				echo '</div>'; //einde wrapperdiv
				break;
		}
	}

}
