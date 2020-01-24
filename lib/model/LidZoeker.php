<?php

namespace CsrDelft\model;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\lid\LLCSV;
use CsrDelft\view\lid\LLKaartje;
use CsrDelft\view\lid\LLLijst;


/**
 * LidZoeker
 *
 * de array's die in deze class staan bepalen wat er in het formulier te zien is.
 */
class LidZoeker {
	//velden die door gewone leden geselecteerd mogen worden.
	private $allowVelden = array(
		'pasfoto', 'uid', 'naam', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'nickname', 'geslacht',
		'email', 'adres', 'telefoon', 'mobiel', 'studie', 'status',
		'gebdatum', 'beroep', 'verticale', 'lidjaar', 'kring', 'patroon', 'woonoord');
	//velden die ook door mensen met P_LEDEN_MOD bekeken mogen worden
	//(merge in de constructor)
	private $allowVeldenLEDENMOD = array(
		'eetwens', 'moot',
		'muziek', 'ontvangtcontactueel', 'kerk', 'lidafdatum',
		'echtgenoot', 'adresseringechtpaar', 'land', 'bankrekening', 'machtiging');
	//deze velden kunnen we niet selecteren voor de ledenlijst, ze zijn wel te
	//filteren en te sorteren.
	private $veldenNotSelectable = array('voornaam', 'achternaam', 'tussenvoegsel');
	//velden die wel selecteerbaar zijn, maar niet in de db bestaan
	private $veldenNotindb = array('pasfoto');
	//nette aliassen voor kolommen, als ze niet beschikbaar zijn wordt gewoon
	//de naam uit $this->allowVelden gebruikt
	public $veldNamen = array(
		'telefoon' => 'Nummer',
		'mobiel' => 'Pauper',
		'studie' => 'Studie',
		'gebdatum' => 'Geb.datum',
		'ontvangtcontactueel' => 'Contactueel?',
		'machtiging' => 'Machtiging getekend?',
		'adresseringechtpaar' => 'Post echtpaar t.n.v.',
		'linkedin' => 'LinkedIn',
	);
	//toegestane opties voor het statusfilter.
	private $allowStatus;
	//toegestane opties voor de weergave.
	private $allowWeergave = [
		'lijst' => LLLijst::class,
		'kaartje' => LLKaartje::class,
		'csv' => LLCSV::class
	];
	private $sortable = array(
		'achternaam' => 'Achternaam',
		'email' => 'Email',
		'gebdatum' => 'Geboortedatum',
		'lidjaar' => 'lichting',
		'studie' => 'Studie'
	);
	//standaardwaarden voor het zoeken zonder parameters
	private $rawQuery = array('status' => 'LEDEN', 'sort' => 'achternaam');
	private $query = '';
	private $filters = array();
	private $sort = array('achternaam');
	private $velden = array('naam', 'email', 'telefoon', 'mobiel');
	private $weergave = LLLijst::class;
	private $result = null;

	public function __construct() {
		$this->allowStatus = LidStatus::getTypeOptions();

		//wat extra velden voor moderators.
		if (LoginModel::mag(P_LEDEN_MOD)) {
			$this->allowVelden = array_merge($this->allowVelden, $this->allowVeldenLEDENMOD);
		}

		//parse default values.
		$this->parseQuery($this->rawQuery);
	}

	public function parseQuery($query) {
		$this->result = null; //nieuwe parameters, oude resultaat wegmikken.

		if (!is_array($query)) {
			$query = explode('&', $query);
		}
		$this->rawQuery = $query;

		//als er geen explicite status is opgegeven, en het zoekende lid is oudlid, dan zoeken we automagisch ook in de oudleden.
		if (!isset($query['status']) AND LoginModel::getProfiel()->isOudlid()) {
			$this->rawQuery['status'] = 'LEDEN|OUDLEDEN';
		}

		foreach ($this->rawQuery as $key => $value) {
			switch ($key) {

				case 'q':
					$this->query = $value;
					break;

				case 'weergave':
					if (isset($this->allowWeergave[$value])) {
						$this->weergave = $this->allowWeergave[$value];
					}
					break;

				case 'velden':
					$this->velden = array();
					foreach ($value as $veld) {
						if (array_key_exists($veld, $this->getSelectableVelden())) {
							$this->velden[] = $veld;
						}
					}
					if (count($this->velden) == 0) {
						$this->velden = array('naam', 'adres', 'email', 'mobiel');
					}
					break;

				case 'status':
					$value = strtoupper($value);
					//als op alle lid-statussen moet worden gezocht verwijderen we
					//eventueel aanwezige filters en zoeken we in alles.
					if ($value == '*' OR $value == 'ALL') {
						if (isset($this->filters['status'])) {
							unset($this->filters['status']);
						}
						break;
					}
					$filters = explode('|', $value);

					$add = array();
					foreach ($filters as $filter) {
						if ($filter == 'LEDEN') {
							$add = array_merge($add, LidStatus::getLidLike());
							continue;
						}
						if ($filter == 'OUDLEDEN') {
							$add = array_merge($add, LidStatus::getOudlidLike());
							continue;
						}
						$filter = 'S_' . $filter;
						if (in_array($filter, $this->allowStatus)) {
							$add[] = $filter;
						}
					}
					$this->addFilter('status', $add);
					break;

				case 'sort':
					if (array_key_exists($value, $this->getSortableVelden())) {
						$this->sort = array($value);
					}
					break;
			}
		}
	}

	//lijst met velden die bruikbaar zijn in een '<veld>:=?<zoekterm>'-zoekopdracht.
	private function getDBVeldenAllowed() {

		//hier staat eigenlijk $a - $b, maar die heeft php niet.
		return array_intersect(array_diff($this->allowVelden, $this->veldenNotindb), $this->allowVelden);
	}

	/**
	 * Stel een setje WHERE-voorwaarden samen waarin standaard wordt gezocht.
	 */
	private function defaultSearch($zoekterm) {
		$query = '';
		$defaults = [];
		$params = [];

		if ($zoekterm == '*' OR trim($zoekterm) == '') {
			$query = '1 ';
		} elseif (preg_match('/^groep:([0-9]+|[a-z]+)$/i', $zoekterm)) { //leden van een groep
			$uids = array();
			/*try {
				//FIXME: $groep = new OldGroep(substr($zoekterm, 6));
				$uids = array_keys($groep->getLeden());
			} catch (\Exception $e) {
				//care.
			}*/
			$query = "uid IN(" . implode(",", $uids) . ") ";
		} elseif (preg_match('/^verticale:\w*$/', $zoekterm)) { //verticale, id, letter
			$v = substr($zoekterm, 10);
			if (strlen($v) > 1) {
				$result = VerticalenModel::instance()->find('naam LIKE ?', [sql_contains($v)]);
				$query = array();
				foreach ($result as $v) {
					$query[] = 'verticale = ? ';
					$params[] = $v->letter;
				}
				$query = '(' . implode(' OR ', $query) . ') ';
			} else {
				$verticale = VerticalenModel::instance()->get($v);
				if ($verticale) {
					$query = 'verticale= ? ';
					$params[] = $verticale->letter;
				} else {
					$query = 'verticale="" ';
				}
			}
		} elseif (preg_match('/^\d{2}$/', $zoekterm)) { //lichting bij een string van 2 cijfers
			$query = "RIGHT(lidjaar,2)=" . (int)$zoekterm . " ";
		} elseif (preg_match('/^lichting:\d\d(\d\d)?$/', $zoekterm)) { //lichting op de explicite manier
			$lichting = substr($zoekterm, 9);
			if (strlen($lichting) == 4) {
				$query = "lidjaar=" . $lichting . " ";
			} else {
				$query = "RIGHT(lidjaar,2)=" . (int)$lichting . " ";
			}
		} elseif (preg_match('/^[a-z0-9][0-9]{3}$/', $zoekterm)) { //uid's is ook niet zo moeilijk.
			$query = "uid='" . $zoekterm . "' ";
		} elseif (preg_match('/^([a-z0-9][0-9]{3} ?,? ?)*([a-z0-9][0-9]{3})$/', $zoekterm)) {
			//meerdere uid's gescheiden door komma's.
			//explode en trim() elke waarde van de array.
			$uids = array_map('trim', explode(',', $zoekterm));
			$query = "uid IN('" . implode("','", $uids) . "') ";
		} elseif (preg_match('/^(' . implode('|', $this->getDBVeldenAllowed()) . '):=?([a-z0-9\-_])+$/i', $zoekterm)) {
			//Zoeken in de velden van $this->allowVelden. Zoektermen met 'veld:' ervoor.
			//met 'veld:=<zoekterm> wordt exact gezocht.
			$parts = explode(':', $zoekterm);

			if ($parts[1][0] == '=') {
				$query = $parts[0] . "= ?";
				$params[] = substr($parts[1], 1);
			} else {
				$query = $parts[0] . " LIKE ?";
				$params[] = sql_contains($parts[1]);
			}
		} else { //als niets van hierboven toepasselijk is zoeken we in zo ongeveer alles
			$defaults[] = "voornaam LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "achternaam LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "CONCAT_WS(' ', voornaam, tussenvoegsel, achternaam) LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "CONCAT_WS(' ', voornaam, achternaam) LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "CONCAT_WS(' ', tussenvoegsel, achternaam) LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "CONCAT_WS(', ', achternaam, tussenvoegsel) LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "nickname LIKE ? ";
			$params[] = sql_contains($zoekterm);
			if (LoginModel::mag(P_LEDEN_MOD)) {
				$defaults[] = "eetwens LIKE ? ";
				$params[] = sql_contains($zoekterm);
			}

			$defaults[] = "CONCAT_WS(' ', adres, postcode, woonplaats) LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "adres LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "postcode LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "woonplaats LIKE ? ";
			$params[] = sql_contains($zoekterm);

			$defaults[] = "mobiel LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "telefoon LIKE ? ";
			$params[] = sql_contains($zoekterm);

			$defaults[] = "studie LIKE ? ";
			$params[] = sql_contains($zoekterm);
			$defaults[] = "email LIKE ? ";
			$params[] = sql_contains($zoekterm);

			$query .= '( ' . implode(' OR ', $defaults) . ' )';
		}

		return array($params, $query . ' AND ');
	}

	/**
	 * Doe de zoektocht.
	 */
	public function search() {
		$query = '';
		$params = [];
		$this->result = [];

		if ($this->query != '') {
			list($paramsPart, $queryPart) = $this->defaultSearch($this->query);
			$query .= $queryPart;
			$params = array_merge($params, $paramsPart);
		}
		list($paramsPart, $queryPart) = $this->getFilterSQL();
		$query .= $queryPart;
		$params = array_merge($params, $paramsPart);

		$result = ContainerFacade::getContainer()->get(ProfielRepository::class)->ormFind($query, $params, null, implode($this->sort));

		foreach ($result as $profiel) {
			if ($this->zoekMag($profiel, $this->query)) {
				$this->result[] = $profiel;
			}
		}
	}

	public function count() {
		if ($this->result === null) {
			$this->search();
		}
		return count($this->result);
	}

	public function searched() {
		return $this->result !== null;
	}

	public function getLeden() {
		if ($this->result === null) {
			$this->search();
		}
		return $this->result;
	}

	public function getQuery() {
		return $this->query;
	}

	public function getVelden() {
		return $this->velden;
	}

	public function getWeergave() {
		return $this->weergave;
	}

	public function getRawQuery($key) {
		if (!isset($this->rawQuery[$key])) {
			return false;
		}
		return $this->rawQuery[$key];
	}

	/*
	 * Zet een array met $key => value om in SQL. Als $value een array is,
	 * komt er een $key IN ( value0, value1, etc. ) uit.
	 */

	public function getFilterSQL() {
		$params = [];
		$filters = array();
		foreach ($this->filters as $key => $value) {
			if (is_array($value)) {
				$filters[] = $key . " IN (" . implode(", ", array_map(function ($val) { return '?';}, $value)) . ")";
				$params = array_merge($params, $value);
			} else {
				$filters[] = $key . "= ?";
				$params[] = $value;
			}
		}
		$return = implode(' AND ', $filters);
		if (strlen(trim($return)) == 0) {
			return [[], '1'];
		} else {
			return [$params, $return];
		}
	}

	public function getSelectedVelden() {
		return $this->velden;
	}

	public function getSelectableVelden() {
		$return = array();
		foreach ($this->allowVelden as $veld) {
			if (in_array($veld, $this->veldenNotSelectable)) {
				continue;
			}
			if (isset($this->veldNamen[$veld])) {
				$return[$veld] = $this->veldNamen[$veld];
			} else {
				$return[$veld] = $veld;
			}
		}
		return $return;
	}

	public function getSortableVelden() {
		return $this->sortable;
	}

	public function addFilter($field, $value) {
		if (is_array($value)) {
			$this->filters[$field] = $value;
		} else {
			$this->filters[$field] = array($value);
		}
	}

	public function __toString() {
		$return = 'Zoeker:';
		$return .= print_r($this->rawQuery, true);
		$return .= print_r($this->filters, true);
		return $return;
	}

	/**
	 * Geef terug of een bepaald resultaat in de zoekresultaten mag zitten.
	 *
	 * @param $profiel
	 * @param string $query
	 * @return Profiel|null
	 */
	private function zoekMag(Profiel $profiel, string $query) {
		// Als de zoekquery in de naam zit, geef dan altijd dit profiel terug als resultaat.
		$lidToestemmingRepository = ContainerFacade::getContainer()->get(LidToestemmingRepository::class);
		$zoekvelden = $lidToestemmingRepository->getModuleKeys('profiel');
		foreach ($zoekvelden as $veld) {
			if ($veld === 'status') {
				continue;
			}

			if (!is_zichtbaar($profiel, $veld)) {
				$queryNietInNaam = $query !== '' && strpos($profiel->getNaam(), $query) === false;
				$queryInVeld = $query !== '' && strpos($profiel->$veld, $query) !== false;

				if ($queryNietInNaam && $queryInVeld) {
					return null;
				} else {
					$profiel->$veld = null;
				}
			}
		}

		return $profiel;
	}

}
