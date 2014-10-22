<?php

require_once 'groepen/groep.class.php';

# C.S.R. Delft
# -------------------------------------------------------------------
# class.ledenlijstcontent.php
# -------------------------------------------------------------------

class EetplanContent extends SmartyTemplateView {

	public function __construct(&$eetplan) {
		parent::__construct($eetplan, 'Eetplan');
	}

	function viewEetplanVoorPheut($uid) {
		//huizen voor een feut tonen
		$aEetplan = $this->model->getEetplanVoorPheut($uid);
		if ($aEetplan === false) {
			echo '<h1>Ongeldig uid</h1>';
		} else {
			echo '<h2><a href="/actueel/eetplan/">Eetplan</a> &raquo; voor ' . Lid::naamLink($uid, 'full', 'plain') . '</h2>
				Profiel van ' . Lid::naamLink($uid, 'civitas', 'link') . '<br /><br />';
			echo '<table class="eetplantabel">
				<tr><th style="width: 150px">Avond</th><th style="width: 200px">Huis</th></tr>';
			$row = 0;
			foreach ($aEetplan as $aEetplanData) {
				$huis = new OldGroep($aEetplanData['groepid']);
				echo '
					<tr class="kleur' . ($row % 2) . '">
						<td >' . $this->model->getDatum($aEetplanData['avond']) . '</td>
						<td><a href="/actueel/eetplan/huis/' . $aEetplanData['huisID'] . '"><strong>' . mb_htmlentities($aEetplanData['huisnaam']) . '</strong></a><br />';
				if ($huis instanceof OldGroep AND $huis->getId() != 0) {
					echo 'Huispagina van ' . $huis->getLink();
				}
				echo '</td></tr>';
				$row++;
			}
			echo '</table>';
		}
	}

	function viewEetplanVoorHuis($iHuisID) {
		//feuten voor een huis tonen
		$aEetplan = $this->model->getEetplanVoorHuis($iHuisID);


		if ($aEetplan === false) {
			echo '<h1>Ongeldig huisID</h1>';
		} else {
			try {
				$huis = new OldGroep($aEetplan[0]['groepid']);
			} catch (Exception $e) {
				$huis = new OldGroep(0); //hmm, dirty 
			}
			$sUitvoer = '<table class="eetplantabel">
				<tr>
				<th style="width: 150px">Avond</th>
				<th style="width: 200px">&Uuml;bersjaarsch </th>
				<th>Mobiel</th>
				<th>E-mail</th>
				<th>Eetwens</th>
				</tr>';
			$iHuidigAvond = 0;
			$row = 0;
			foreach ($aEetplan as $aEetplanData) {
				if ($aEetplanData['avond'] == $iHuidigAvond) {
					$ertussen = '&nbsp;';
				} else {
					$ertussen = $this->model->getDatum($aEetplanData['avond']);
					$iHuidigAvond = $aEetplanData['avond'];
					$row++;
				}
				$sUitvoer .= '
					<tr class="kleur' . ($row % 2) . '">
						<td>' . $ertussen;
				$sUitvoer .= '</td>
					<td>' . Lid::naamLink($aEetplanData['pheut'], 'civitas', 'link') . '<br /></td>
					<td>' . mb_htmlentities($aEetplanData['mobiel']) . '</td>
					<td>' . mb_htmlentities($aEetplanData['email']) . '</td>
					<td>' . mb_htmlentities($aEetplanData['eetwens']) . '</td>
					</tr>';
			}
			$sUitvoer .= '</table>';
			echo '<h2><a href="/actueel/eetplan/">Eetplan</a> &raquo; voor ' . mb_htmlentities($aEetplanData['huisnaam']) . '</h2>
				' . mb_htmlentities($aEetplanData['huisadres']) . ' <br />';
			if ($huis instanceof OldGroep AND $huis->getId() != 0) {
				echo 'Huispagina: ' . $huis->getLink() . '<br /><br />';
			}
			echo $sUitvoer;
		}
	}

	function viewEetplan($aEetplan) {
		$aHuizenArray = $this->model->getHuizen();
		//weergeven
		echo '
			<h1>Eetplan</h1>
			<div class="geelblokje"><h2>LET OP: </h2>
				Van eerstejaers die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.
			</div>
			<table class="eetplantabel">
			<tr><th style="width: 200px;">&Uuml;bersjaarsch/Avond</td>';
		//kopjes voor tabel
		for ($iTeller = 5; $iTeller <= 8; $iTeller++) {
			echo '<th class="huis">' . $this->model->getDatum($iTeller) . '</th>';
		}
		echo '</tr>';
		$row = 0;
		foreach ($aEetplan as $aEetplanVoorPheut) {


			echo '<tr class="kleur' . ($row % 2) . '"><td><a href="/actueel/eetplan/sjaars/' . $aEetplanVoorPheut[0]['uid'] . '">' . $aEetplanVoorPheut[0]['naam'] . '</a></td>';
			for ($iTeller = 1; $iTeller <= 3; $iTeller++) {
				$huisnaam = $aHuizenArray[$aEetplanVoorPheut[$iTeller] - 1]['huisNaam'];
				$huisnaam = str_replace(array('Huize ', 'De ', 'Villa '), '', $huisnaam);
				$huisnaam = substr($huisnaam, 0, 18);

				echo '<td class="huis"><a href="/actueel/eetplan/huis/' . $aEetplanVoorPheut[$iTeller] . '">' .
				mb_htmlentities($huisnaam) .
				'</a></td>';
			}
			echo '</tr>';
			$row++;
		}
		echo '</table>';
		//nog even een huizentabel erachteraan

		echo '<br /><h1>Huizen met hun nummers:</h1>
			<table class="eetplantabel">
				<tr><th>Naam</th><th>Adres</th><th>Telefoon</th></tr>';

		foreach ($aHuizenArray as $aHuis) {
			try {
				$huis = new OldGroep($aHuis['groepid']);
			} catch (Exception $e) {
				$huis = new OldGroep(0);
			}

			echo '<tr class="kleur' . ($row % 2) . '">';
			echo '<td><a href="/actueel/eetplan/huis/' . $aHuis['huisID'] . '">' . mb_htmlentities($aHuis['huisNaam']) . '</a></td><td>';
			if ($huis instanceof OldGroep AND $huis->getId() != 0) {
				echo $huis->getLink();
			}
			echo '</td><td>' . $aHuis['telefoon'] . '</td></tr>';
			$row++;
		}
		echo '</table>';
	}

	function view() {
		//kijken of er een pheut of een huis gevraagd wordt, of een overzicht.
		if (isset($_GET['pheutID'])) {
			//eetplanavonden voor een pheut tonen
			$iPheutID = $_GET['pheutID'];
			$this->viewEetplanVoorPheut($iPheutID);
		} elseif (isset($_GET['huisID'])) {
			//pheuten voor een huis tonen
			$iHuisID = (int) $_GET['huisID'];
			$this->viewEetplanVoorHuis($iHuisID);
		} else {
			//standaard actie, gewoon overzicht tonen.
			$aEetplan = $this->model->getEetplan();
			$this->viewEetplan($aEetplan);
		}
	}

}
