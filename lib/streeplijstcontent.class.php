<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.streeplijst.php
# -------------------------------------------------------------------
# Kan streep/bestellijsten maken.
# -------------------------------------------------------------------

class Streeplijstcontent extends TemplateView {

	private $moot = 'alle';
	private $lichting = '';
	private $aGoederen;
	private $aLeden;

	function __construct() {
		parent::__construct(null);
		$this->load();
	}

	function load() {
		if (isset($_GET['goederen']) AND trim($_GET['goederen']) != '') {
			$sGoederen = htmlspecialchars($_GET['goederen']);
		} else {
			$sGoederen = 'Grolschbier, S.bier, frisfris, reep, mix, sig., wijnF, sterk, B.noot, WW';
		}
		$this->parseGoederen($sGoederen);

		if (isset($_GET['moot']) AND (int) $_GET['moot'] == $_GET['moot']) {
			$this->moot = (int) $_GET['moot'];
		}
		if (isset($_GET['lichting']) AND preg_match('/^\d{2}$/', $_GET['lichting']) == 1) {
			$this->lichting = $_GET['lichting'];
		}
		//leden welke in de lijst moeten laden.
		$this->aLeden = Zoeker::zoekLeden($this->lichting, 'uid', $this->moot, 'achternaam', 'leden');
	}

	function parseGoederen($sGoederen) {
		$sGoederen = str_replace(array(', ', ',  '), ',', $sGoederen);
		$this->aGoederen = explode(',', $sGoederen);
		if (isset($_GET['sortCols'])) {
			sort($this->aGoederen);
		}
	}

	function getGoederenArray() {
		return $this->aGoederen;
	}

	function goederenCount() {
		return count($this->getGoederenArray());
	}

	function getGoederen() {
		return implode(', ', $this->getGoederenArray());
	}

	function getHTML() {
		$sReturn = '
			<html>
				<head>
					<style>
						body{ font-family: arial; font-size: 13px; }
						table{ border: 2px solid black; }
						td{ border: 1px solid black; }
						table{ border-collapse: collapse; width: 100%; }
						td.naam{
							border-right: 2px solid black;
							width: 25%; white-space: no-wrap;
						}
						td.cell0{  }
						td.cell1{ background-color: darkgrey;}
						thead td{
							border-bottom: 2px solid black;
							border-top: 2px solid black;
							font-weight: bold; padding: 2px;}
						.breekpunt{
							page-break-after: always; }

						input.text { width: 100% }

					</style>
			</head>
			<body><table>';
		//headerregeltje klussen
		$sKop = '<thead><tr><td class="naam">Naam</td>';
		$i = 1;
		foreach ($this->aGoederen as $sArtikel) {
			$sKop .= '<td class="cell';
			//switch the row coloring..
			if (isset($_GET['colorCols'])) {
				$sKop.=($i % 2);
			} else {
				$sKop .= '0';
			}
			$sKop .= '">' . $sArtikel . '</td>';
			$i++;
		}
		$sKop .= '</tr></thead>';

		//eerte header weergeven.
		$sReturn.=$sKop;

		$iTeller = 2;
		foreach ($this->aLeden as $aLid) {
			if ($iTeller % 43 == 1) {
				$sReturn.=$sKop . '</tr></table>';
				$sReturn .= '<span class="breekpunt"></span>';
				$sReturn .= '<table><tr>' . $sKop;
			}
			$sReturn .= '<tr><td class="naam">' . Lid::naamLink($aLid['uid'], 'streeplijst', 'plain') . '</td>';
			for ($i = 1; $i <= $this->goederenCount(); $i++) {
				$sReturn .= '<td class="cell' . ($i % 2) . '">&nbsp;</td>';
			}
			$sReturn .= '</tr>' . "\r\n";
			$iTeller++;
		}
		$sReturn.=$sKop;
		$sReturn .= '</table>';

		return $sReturn;
	}

	function getPdf() {
		
	}

	function getUrl() {
		$sReturn = 'streeplijst.php?goederen=' . urlencode($this->getGoederen()) .
				'&moot=' . $this->moot . '&lichting=' . $this->lichting . '&';
		if (isset($_GET['colorCols'])) {
			$sReturn .= 'colorCols&';
		}
		if (isset($_GET['sortCols'])) {
			$sReturn .= 'sortCols&';
		}
		return $sReturn;
	}

	function view() {
		echo '<h1>Bestel- &amp; inschrijflijst-generator voor C.S.R. Delft</h1>
			<form id="streeplijst" action="streeplijst.php" method="get">
			<fieldset>
				<legend>Bestellijst</legend>
				<br />
				<strong>Goederen:</strong> (Voer goederen in gescheiden door een komma.)<br />
				<input type="text" name="goederen" value="' . $this->getGoederen() . '" style="width: 100%;" /><br />
				<br />
			</fieldset>
			<br />
			<fieldset>
				<legend>Ledenselectie</legend><br />';
		//mootselectie
		echo '<strong>Verticale:</strong><br />';
		$moten = array_merge(array('alle'), range(1, 8));
		foreach ($moten as $moot) {
			echo '<input type="radio" name="moot" id="m' . $moot . '" value="' . $moot . '" ';
			if ($moot == $this->moot) {
				echo 'checked="checked" ';
			}
			echo '/> <label for="m' . $moot . '">';
			if ($moot == 'alle') {
				echo $moot;
			} else {
				echo Verticale::getNaamById($moot);
			}
			echo '</label>';
		}
		echo '<br />';
		//lichtingsselectie
		echo '<strong>Lichting:</strong><br />';
		$jaren = array_merge(array('alle'), range(date('Y') - 7, date('Y')));
		foreach ($jaren as $jaar) {
			echo '<input type="radio" name="lichting" id="l' . $jaar . '" value="' . substr($jaar, 2) . '" ';
			if (substr($jaar, 2) == $this->lichting) {
				echo 'checked="checked" ';
			}
			echo '/> <label for="l' . $jaar . '">' . $jaar . '</label>';
		}
		echo '</fieldset>
			<br />
			<fieldset>
				<legend>Leguit</legend>
				<input type="checkbox" name="colorCols" id="colorCols" value="" checked="checked" />
				<label for="colorCols">Kolommen om en om grijs maken.</label><br />
				<input type="checkbox" name="sortCols" id="sortCols" value="" />
				<label for="sortCols">Goederen alfabetisch sorteren.</label><br />
				<br /><input type="submit" name="toon" value="Laeden" />
			</fieldset>
			</form>';
		echo '<br />
			Aandachtspunten bij printen via Firefox (andere browsers nog geen uitleg beschikbaar):
			<ul>
				<li>In de <i>Bestand</i> > <i>Pagina-instellingen</i>:
				<ul>
					<li>A4 selecteren als juiste papiergrootte<br /> </li>
				</ul>
				</li>
				<li>In de <i>Opties</i> van <i>Afdruk</i>instellingen (verschijnt via <i>Bestand</i> > <i>Afdrukken...</i>)
				<ul>
					<li>De headers en footers allemaal op <i>blanco</i> zetten</li>
					<li>Achtergrondkleuren afdrukken <i>aan</i>vinken</li>
				</ul>
				</li>
			</ul><br />';
		if (isset($_GET['toon'])) {
			echo '<a href="' . $this->getUrl() . 'iframe">Alleen de streeplijst</a><br />';
			//iframe met html meuk...
			echo '<iframe style="width: 100%; height: 400px;" src="' . $this->getUrl() . 'iframe"></iframe>';
		}
	}

}
