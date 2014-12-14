<?php

require_once 'model/EetplanModel.class.php';
require_once 'model/entity/groepen/OldGroep.class.php';

/**
 * EetplanView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Weergeven van eetplan.
 */
abstract class AbstractEetplanView implements View {

	protected $model;
	protected $aEetplan;

	public function __construct(EetplanModel $model) {
		$this->model = $model;
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return 'Eetplan';
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><img src="' . CSR_PICS . '/knopjes/home-16.png" class="module-icon"></a> » <a href="/eetplan">Eetplan</a>';
	}

}

class EetplanView extends AbstractEetplanView {

	public function __construct(EetplanModel $model) {
		parent::__construct($model);
		$this->aEetplan = $this->model->getEetplan();
	}

	function view() {
		$aToonAvonden = array(1, 2, 3);
		$aHuizenArray = $this->model->getHuizen();
		echo '
			<h1>Eetplan</h1>
			<div class="geelblokje"><h3>LET OP: </h3>
				Van novieten die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.
			</div>
			<table class="eetplantabel">
			<tr><th style="width: 200px;">Noviet/Avond</td>';
		//kopjes voor tabel
		foreach ($aToonAvonden as $iTeller) {
			echo '<th class="huis">' . $this->model->getDatum($iTeller) . '</th>';
		}
		echo '</tr>';
		$row = 0;
		foreach ($this->aEetplan as $aEetplanVoorPheut) {
			echo '<tr class="kleur' . ($row % 2) . '"><td><a href="/eetplan/noviet/' . $aEetplanVoorPheut[0]['uid'] . '">' . $aEetplanVoorPheut[0]['naam'] . '</a></td>';
			foreach ($aToonAvonden as $iTeller) {
				$huisnaam = $aHuizenArray[$aEetplanVoorPheut[$iTeller] - 1]['huisNaam'];
				$huisnaam = str_replace(array('Huize ', 'De ', 'Villa '), '', $huisnaam);
				$huisnaam = substr($huisnaam, 0, 18);

				echo '<td class="huis"><a href="/eetplan/huis/' . $aEetplanVoorPheut[$iTeller] . '">' .
				htmlspecialchars($huisnaam) .
				'</a></td>';
			}
			echo '</tr>';
			$row++;
		}
		echo '</table>';
		//nog even een huizentabel erachteraan
		echo '<h2>Huizen</h2>
			<table class="eetplantabel">
				<tr><th>Naam</th><th>Huispagina</th></tr>';
		foreach ($aHuizenArray as $aHuis) {
			try {
				$huis = new OldGroep($aHuis['groepid']);
			} catch (Exception $e) {
				$huis = new OldGroep(0);
			}
			echo '<tr class="kleur' . ($row % 2) . '">';
			echo '<td><a href="/eetplan/huis/' . $aHuis['huisID'] . '">' . htmlspecialchars($aHuis['huisNaam']) . '</a></td><td>';
			if ($huis instanceof OldGroep AND $huis->getId() != 0) {
				echo $huis->getLink();
			}
			echo '</td></tr>';
			$row++;
		}
		echo '</table>';
	}

}

class EetplanNovietView extends AbstractEetplanView {

	private $uid;

	public function __construct(EetplanModel $model, $uid) {
		parent::__construct($model);
		$this->uid = $uid;
		$this->aEetplan = $this->model->getEetplanVoorPheut($this->uid);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . Lid::naamLink($this->uid, 'civitas', 'link');
	}

	function view() {
		//huizen voor een feut tonen
		if ($this->aEetplan === false) {
			echo '<h3>Ongeldig uid</h3>';
		} else {
			echo '<table class="eetplantabel">
				<tr><th style="width: 150px">Avond</th><th style="width: 200px">Huis</th></tr>';
			$row = 0;
			foreach ($this->aEetplan as $aEetplanData) {
				$huis = new OldGroep($aEetplanData['groepid']);
				echo '<tr class="kleur' . ($row % 2) . '">
						<td >' . $this->model->getDatum($aEetplanData['avond']) . '</td><td>';
				if ($huis instanceof OldGroep AND $huis->getId() != 0) {
					echo $huis->getLink();
				}
				echo '</td></tr>';
				$row++;
			}
			echo '</table>';
		}
	}

}

class EetplanHuisView extends AbstractEetplanView {

	private $huis;

	public function __construct(EetplanModel $model, $iHuisID) {
		parent::__construct($model);
		$this->aEetplan = $this->model->getEetplanVoorHuis($iHuisID);
		try {
			$this->huis = new OldGroep($this->aEetplan[0]['groepid']);
		} catch (Exception $e) {
			$this->huis = new OldGroep(0); //hmm, dirty 
		}
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . $this->huis->getLink();
	}

	function view() {
		//feuten voor een huis tonen
		if ($this->aEetplan === false) {
			echo '<h3>Ongeldig huisID</h3>';
		} else {
			echo '<table class="eetplantabel">
				<tr>
				<th style="width: 150px">Avond</th>
				<th style="width: 200px">&Uuml;bersjaarsch </th>
				<th>Mobiel</th>
				<th>E-mail</th>
				<th>Eetwens</th>
				</tr>';
			$iHuidigAvond = 0;
			$row = 0;
			foreach ($this->aEetplan as $aEetplanData) {
				if ($aEetplanData['avond'] == $iHuidigAvond) {
					$ertussen = '&nbsp;';
				} else {
					$ertussen = $this->model->getDatum($aEetplanData['avond']);
					$iHuidigAvond = $aEetplanData['avond'];
					$row++;
				}
				echo '<tr class="kleur' . ($row % 2) . '"><td>' . $ertussen . '</td>
					<td>' . Lid::naamLink($aEetplanData['pheut'], 'civitas', 'visitekaartje') . '</td>
					<td>' . htmlspecialchars($aEetplanData['mobiel']) . '</td>
					<td>' . htmlspecialchars($aEetplanData['email']) . '</td>
					<td>' . htmlspecialchars($aEetplanData['eetwens']) . '</td>
					</tr>';
			}
			echo '</table>';
		}
	}

}
