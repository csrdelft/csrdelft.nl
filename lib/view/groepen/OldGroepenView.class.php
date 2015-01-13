<?php

require_once 'model/entity/groepen/OldGroep.class.php';

/**
 * OldGroepView.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Een verzameling contentclassen voor de groepenketzer.
 *
 * GroepContent					Weergeven van een groep & bewerken en etc.
 * Groepencontent				Weergeven van een groepenoverzicht
 * Groepengeschiedeniscontent	Weergeven van een mooie patchwork van groepjes.
 * GroepenProfielConcent		Weergeven van groepenlijstje in profiel
 * GroepBBContent				Weergeven van enkele zaken van een groep met bbcode
 */
abstract class OldGroepView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><img src="/plaetjes/knopjes/group-16.png" class="module-icon"></a>';
	}

}

class GroepContent extends OldGroepView {

	private $action = 'view';
	private $groeptype;
	private $groeptypes;

	public function __construct(OldGroep $groep, $titel = false) {
		parent::__construct($groep, $titel);
		$this->groeptypes = GroepenOldModel::getGroeptypes();
		foreach ($this->groeptypes as $type) {
			if ($type['id'] == $groep->getTypeId()) {
				$this->groeptype = $type['naam'];
			}
		}
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getTitel() {
		return $_GET['gtype'] . ' - ' . $this->model->getNaam();
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/groepen/' . $this->groeptype . '">' . $this->groeptype . '</a> » <span class="active">' . $this->model->getNaam() . '</span>';
	}

	/**
	 * Deze functie geeft een formulierding voor het eenvoudig toevoegen van leden
	 * aan een bepaalde groep.
	 */
	private function getLidAdder() {
		if (isset($_POST['rawNamen']) AND trim($_POST['rawNamen']) != '') {
			$return = '';

			//uitmaken waarin we allemaal zoeken, standaard in de normale leden, wellicht
			//ook in oudleden en nobodies
			$zoekin = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL');
			if (isset($_POST['filterOud'])) {
				$zoekin[] = LidStatus::Oudlid;
				$zoekin[] = LidStatus::Erelid;
			}
			if (isset($_POST['filterNobody']) AND $this->model->isAdmin()) {
				$zoekin[] = LidStatus::Nobody;
				$zoekin[] = LidStatus::Exlid;
				$zoekin[] = LidStatus::Overleden;
				$zoekin[] = LidStatus::Commissie;
			}
			$leden = namen2uid($_POST['rawNamen'], $zoekin);

			if (is_array($leden) AND count($leden) != 0) {
				$return .= '<table border="0">';

				foreach ($leden as $aGroepUid) {
					if (isset($aGroepUid['uid'])) {
						//naam is gevonden en uniek, dus direct goed.
						$return .= '<tr>';
						$return .= '<td><input type="hidden" name="naam[]" value="' . $aGroepUid['uid'] . '" />' . $aGroepUid['naam'] . '</td>';
					} else {
						//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
						if (count($aGroepUid['naamOpties']) > 0) {
							$return .= '<tr><td><select name="naam[]" class="breed">';
							foreach ($aGroepUid['naamOpties'] as $aNaamOptie) {
								$return .= '<option value="' . $aNaamOptie['uid'] . '">' . $aNaamOptie['naam'] . '</option>';
							}
							$return .= '</select></td>';
						}//dingen die niets opleveren wordt niets voor weergegeven.
					}
					if ($this->model->magBewerken()) {
						$return .= '<td><input type="text" maxlength="25" name="functie[]" /></td></tr>';
					} else {
						$return .= '<td>' . $this->getFunctieSelector() . '</td></tr>';
					}
				}
				$return .= '</table>';
				return $return;
			}
		}
		return false;
	}

	/**
	 * Niet-admins kunnen kiezen uit een van te voren vastgesteld lijstje met functies, zodat
	 * we  niet allerlei onzinnamen krijgen zoals Kücherführer enzo.
	 */
	private function getFunctieSelector($uid = '') {
		$return = '';
		$aFuncties = array('Q.Q.', 'Praeses', 'Fiscus', 'Redacteur', 'Computeur', 'Archivaris',
			'Bibliothecaris', 'Statisticus', 'Fotocommissaris', '', 'Koemissaris', 'Regisseur',
			'Lichttechnicus', 'Geluidstechnicus', 'Adviseur', 'Internetman', 'Posterman',
			'Corveemanager', 'Provisor', 'HO', 'HJ', 'Onderhuurder');
		sort($aFuncties);
		$return .= '<select name="functie[]" class="breed">';
		foreach ($aFuncties as $sFunctie) {
			$return .= '<option value="' . $sFunctie . '"';
			if ($sFunctie == $this->model->getFunctie($uid)) {
				$return .= 'selected="selected"';
			}
			$return .= '>' . $sFunctie . '</option>';
		}
		$return .= '</select>';
		return $return;
	}

	public function getAanmeldfilters() {
		$filters = array(
			''				 => 'Niet aanmeldbaar',
			'P_LOGGED_IN'	 => 'Alle leden',
			'geslacht:m'	 => 'Alleen mannen',
			'geslacht:v'	 => 'Alleen vrouwen');

		// Verticalen
		foreach (VerticalenModel::instance()->prefetch() as $verticale) {
			if ($verticale->letter == '') {
				continue;
			}
			$filter = 'verticale:' . $verticale->letter;
			$filters[$filter] = 'Verticale ' . $verticale->naam;
		}

		// Lichtingen
		$nu = Lichting::getJongsteLichting();
		for ($lichting = $nu; $lichting >= ($nu - 7); $lichting--) {
			$filters['lichting:' . $lichting] = 'Lichting ' . $lichting;
		}

		return $filters;
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('opvolgerVoorganger', $this->model->getOpvolgerVoorganger());
		$this->smarty->assign('action', $this->action);
		$this->smarty->assign('groeptypes', $this->groeptypes);
		$this->smarty->assign('aanmeldfilters', $this->getAanmeldfilters());
		$oud = null;
		if (isset($_SESSION['oudegroep'])) {
			$oud = $_SESSION['oudegroep'];
		}
		$this->smarty->assign('oudegroep', $oud);
		if ($this->action == 'addLid') {
			$this->smarty->assign('lidAdder', $this->getLidAdder());
		}
		$this->smarty->display('groepen/groep.tpl');
	}

}

class OldGroepenView extends OldGroepView {

	private $action = 'view';

	public function setAction($action) {
		$this->action = $action;
	}

	public function getTitel() {
		return 'Groepen - ' . $this->model->getNaam();
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . $this->model->getNaam();
	}

	public function view() {
		$this->smarty->assign('groepen', $this->model);
		$this->smarty->assign('gtype', $this->model->getNaam());
		$this->smarty->assign('groeptypes', GroepenOldModel::getGroeptypes());
		$this->smarty->assign('action', $this->action);
		$this->smarty->display('groepen/groepen.tpl');
	}

}

class GroepledenContent extends OldGroepView {

	private $actie = 'standaard';

	public function __construct(OldGroep $groep, $actie = 'standaard') {
		parent::__construct($groep);
		$this->actie = $actie;
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('groepen/groepleden.tpl');
	}

}

class Groepgeschiedeniscontent extends OldGroepView {

	public function view() {
		$jaren = 5;
		$maanden = $jaren * 12;
		echo '<table>';
		echo '<tr>';
		for ($i = 2008; $i >= (2008 - $jaren); $i--) {
			echo '<td colspan="12">' . $i . '</td>';
		}
		echo '</tr>';
		echo '<tr>';
		for ($i = 0; $i <= $maanden; $i++) {
			echo '<td style="max-width: 10px;">&nbsp;</td>';
		}
		echo '</tr>';
		foreach ($this->model->getGroepen() as $groep) {
			echo '<tr>';
			$startspacer = 12 - substr($groep->getBegin(), 5, 2);
			if ($startspacer != 0) {
				echo '<td colspan="' . $startspacer . '" class="lichtgrijs">(' . $startspacer . ')</td>';
			}

			$oudeGr = OldGroep::getGroepgeschiedenis($groep->getSnaam(), $jaren);
			foreach ($oudeGr as $grp) {
				$duration = $grp['duration'];
				if ($duration <= 0) {
					$duration = 12;
				}
				echo '<td colspan="' . $duration . '" style="border: 1px solid black; padding: 2px; width: 150px; text-align: left;">';
				echo '<a href="/groepen/' . $this->model->getNaam() . '/' . $grp['id'] . '">' . $grp['naam'] . '</a>';

				echo '</td>';
			}
			if (count($oudeGr) < $maanden) {
				$spacer = $maanden - count($oudeGr);
				echo '<td colspan="' . $spacer . '" class="lichtgrijs">&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

}

/**
 * Weergave van groepen in het profiel.
 */
class GroepenProfielContent extends OldGroepView {

	private $display_lower_limit = 8;
	private $display_upper_limit = 12;

	public function getHtml() {
		//per status in een array rammen
		$groepenPerStatus = array();
		foreach (GroepenOldModel::getByUid($this->model) as $groep) {
			$groepenPerStatus[$groep->getStatus()][] = $groep;
		}

		$return = '';
		foreach ($groepenPerStatus as $status => $groepen) {
			$return .= '<div class="groep' . $status . '">';
			$return .= '<h6>' . str_replace(array('ht', 'ot', 'ft'), array('h.t.', 'o.t.', 'f.t.'), $status) . ' groepen:</h6>';
			$return .= '<ul class="groeplijst nobullets">';
			$i = 0;
			$class = '';

			//zorg dat als het aantal tussen onder en bovengrens in zit gewoon alles wordt weergegeven,
			$display_limit = $this->display_lower_limit;
			if (count($groepen) > $this->display_lower_limit AND count($groepen) < $this->display_upper_limit) {
				$display_limit = $this->display_upper_limit;
			}

			foreach ($groepen as $groep) {
				if ($i > $display_limit) {
					$class = ' class="verborgen"';
				}
				//op een of andere manier werkt het hier niet als ik een class-property gebruik,
				//dus daarom maar met inline style.
				$return .= '<li' . $class . '>' . $groep->getLink() . '</li>';
				$i++;
			}

			$return .= '</ul>';
			if ($i > $display_limit) {
				$return .= '<a onclick="jQuery(this).parent().children(\'ul\').children().show(); jQuery(this).remove();">&raquo; meer </a>';
			}

			$return .= '</div>';
		}
		return $return;
	}

	public function view() {
		echo $this->getHtml();
	}

}

/**
 * Contentclasse voor de groep-bbcode-tag
 */
class GroepBBContent extends OldGroepView {

	public function getHtml() {
		$this->smarty->assign('groep', $this->model);
		return $this->smarty->fetch('groepen/groep.bb.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}

class GroepStatsContent extends OldGroepView {

	public function view() {
		$stats = $this->model->getStats();
		if (!is_array($stats)) {
			return;
		}
		echo '<table class="groepStats">';
		foreach ($stats as $title => $stat) {
			if (!is_array($stat)) {
				continue;
			}
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
			$rowColor = false;
			foreach ($stat as $row) {
				$rowColor = (!$rowColor);
				echo '<tr>';
				foreach ($row as $column) {
					echo '<td>' . $column . '</td>';
				}
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
	}

}

class GroepEmailContent extends OldGroepView {

	public function view() {
		$emails = array();
		$groepleden = $this->model->getLeden();
		if (is_array($groepleden)) {
			foreach ($groepleden as $groeplid) {
				$profiel = ProfielModel::get($groeplid['uid']);
				if ($profiel AND $profiel->getPrimaryEmail() != '') {
					$emails[] = $profiel->getPrimaryEmail();
				}
			}
		}
		echo '<div class="emails">' . implode(', ', $emails) . '</div>';
	}

}
