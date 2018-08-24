<?php

namespace CsrDelft\lid;

use CsrDelft\MijnSqli;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;

require_once 'common.functions.php';

/**
 * LidZoeker
 *
 * de array's die in deze class staan bepalen wat er in het formulier te zien is.
 *
 * @deprecated
 */
class LidZoeker {

	/**
	 * @deprecated Dit is de oude zoekfunctie, maar deze functie wordt nog veelvuldig gebruikt...
	 */
	public static function zoekLeden($zoekterm, $zoekveld, $verticale, $sort, $zoekstatus = '', $velden = array(), $limiet = 0) {
		$db = MijnSqli::instance();
		$leden = array();
		$zoekfilter = '';

		# mysql escape dingesen
		$zoekterm = trim($db->escape($zoekterm));
		$zoekveld = trim($db->escape($zoekveld));
		/* TODO: velden checken op rare dingen. Niet dat de velden() array nu buiten code opgegeven kan worden, maar het moet nog wel
		  foreach ($velden as &$veld) {
		  $veld = trim, escape, lalala
		  } */

		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if ($zoekveld == 'naam' AND !preg_match('/^\d{2}$/', $zoekterm)) {
			if (preg_match('/ /', trim($zoekterm))) {
				$zoekdelen = explode(' ', $zoekterm);
				$iZoekdelen = count($zoekdelen);
				if ($iZoekdelen == 2) {
					$zoekfilter = "( voornaam LIKE '%" . $zoekdelen[0] . "%' AND achternaam LIKE '%" . $zoekdelen[1] . "%' ) OR";
					$zoekfilter .= "( voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
                                    nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%' )";
				} else {
					$zoekfilter = "( voornaam LIKE '%" . $zoekdelen[0] . "%' AND achternaam LIKE '%" . $zoekdelen[$iZoekdelen - 1] . "%' )";
				}
			} else {
				$zoekfilter = "
					voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
					nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%'";
			}
		} elseif ($zoekveld == 'adres') {
			$zoekfilter = "adres LIKE '%{$zoekterm}%' OR woonplaats LIKE '%{$zoekterm}%' OR
				postcode LIKE '%{$zoekterm}%' OR REPLACE(postcode, ' ', '') LIKE '%" . str_replace(' ', '', $zoekterm) . "%'";
		} else {
			if (preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld == 'uid' OR $zoekveld == 'naam')) {
				//zoeken op lichtingen...
				$zoekfilter = "SUBSTRING(uid, 1, 2)='" . $zoekterm . "'";
			} else {
				$zoekfilter = "{$zoekveld} LIKE '%{$zoekterm}%'";
			}
		}

		$sort = $db->escape($sort);

		# In welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft.
		#
		# R_LID en R_OUDLID hebben beide P_LEDEN_READ en P_OUDLEDEN_READ en kunnen
		# de volgende afkortingen gebruiken:
		#  - '' (lege string) of alleleden: novieten, (gast)leden, kringels, ere- en oudleden
		#  - leden :  						novieten, (gast)leden en kringels
		#  - oudleden : 					oud- en ereleden
		#  - allepersonen:					novieten, (gast)leden, kringels, oud- en ereleden, overleden leden en nobodies (alleen geen commissies)
		# én alleen voor OUDLEDENMOD:
		#  - nobodies : 					alleen nobodies

		$statusfilter = '';
		if ($zoekstatus == 'alleleden') {
			$zoekstatus = '';
		}
		if ($zoekstatus == 'allepersonen') {
			$zoekstatus = array('S_NOVIET', 'S_LID', 'S_GASTLID', 'S_OUDLID', 'S_ERELID', 'S_KRINGEL', 'S_OVERLEDEN', 'S_NOBODY', 'S_EXLID');
		}
		if (is_array($zoekstatus)) {
			//we gaan nu gewoon simpelweg statussen aan elkaar plakken. LET OP: deze functie doet nu
			//geen controle of een gebruiker dat mag, dat moet dus eerder gebeuren.
			$statusfilter = "status='" . implode("' OR status='", $zoekstatus) . "'";
		} else {
			# we zoeken in leden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
			if (
				(LoginModel::mag('P_LEDEN_READ') and !LoginModel::mag('P_OUDLEDEN_READ')) or (LoginModel::mag('P_LEDEN_READ') and LoginModel::mag('P_OUDLEDEN_READ') and $zoekstatus != 'oudleden')
			) {
				$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
			}
			# we zoeken in oudleden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
			if (
				(!LoginModel::mag('P_LEDEN_READ') and LoginModel::mag('P_OUDLEDEN_READ')) or (LoginModel::mag('P_LEDEN_READ') and LoginModel::mag('P_OUDLEDEN_READ') and $zoekstatus != 'leden')
			) {
				if ($statusfilter != '')
					$statusfilter .= " OR ";
				$statusfilter .= "status='S_OUDLID' OR status='S_ERELID'";
			}
			# we zoeken in nobodies als
			# de ingelogde persoon dat mag EN daarom gevraagd heeft
			if ($zoekstatus === 'nobodies' and LoginModel::mag('P_LEDEN_MOD')) {
				# alle voorgaande filters worden ongedaan gemaakt en er wordt alleen op nobodies gezocht
				$statusfilter = "status='S_NOBODY' OR status='S_EXLID'";
			}

			if (LoginModel::mag('P_LEDEN_READ') and $zoekstatus === 'novieten') {
				$statusfilter = "status='S_NOVIET'";
			}
		}

		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		$mootfilter = ($verticale != 'alle') ? 'AND verticale=\'' . $verticale . '\' ' : '';
		# is er een maximum aantal resultaten gewenst
		if ((int)$limiet > 0) {
			$limit = 'LIMIT ' . (int)$limiet;
		} else {
			$limit = '';
		}

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {

			# standaardvelden
			if (empty($velden)) {
				$velden = array('uid', 'nickname', 'duckname', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix', 'adres', 'postcode', 'woonplaats', 'land', 'telefoon',
					'mobiel', 'email', 'geslacht', 'gebdatum', 'voornamen', 'website', 'beroep', 'studie', 'studiejaar',
					'o_adres', 'o_postcode', 'o_woonplaats', 'o_land', 'o_telefoon', 'kerk', 'muziek', 'eetwens', 'status');
			}

			# velden kiezen om terug te geven
			$velden_sql = implode(', ', $velden);
			$velden_sql = str_replace('corvee_punten_totaal', 'corvee_punten+corvee_punten_bonus AS corvee_punten_totaal', $velden_sql);
			$sZoeken = "
				SELECT
					" . $velden_sql . "
				FROM profielen
				WHERE
					(" . $zoekfilter . ")
				AND
					($statusfilter)
				{$mootfilter}
				ORDER BY
					{$sort}
				{$limit}
			";
			$result = $db->select($sZoeken);

			if ($result !== false and $db->numRows($result) > 0) {
				while ($lid = $db->next($result)) {
					$leden[] = $lid;
				}
			}
		}

		return $leden;
	}

	//velden die door gewone leden geselecteerd mogen worden.
	private $allowVelden = array(
		'pasfoto', 'uid', 'naam', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'nickname', 'duckname', 'geslacht',
		'email', 'adres', 'telefoon', 'mobiel', 'linkedin', 'website', 'studie', 'status',
		'gebdatum', 'beroep', 'verticale', 'moot', 'lidjaar', 'kring', 'patroon', 'woonoord', 'bankrekening', 'eetwens');
	//velden die ook door mensen met P_LEDEN_MOD bekeken mogen worden
	//(merge in de constructor)
	private $allowVeldenLEDENMOD = array(
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
	private $allowStatus = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_NOBODY', 'S_EXLID', 'S_OUDLID', 'S_ERELID', 'S_KRINGEL', 'S_OVERLEDEN');
	//toegestane opties voor de weergave.
	private $allowWeergave = array('lijst', 'kaartje', 'CSV');
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
	private $weergave = 'lijst';
	private $result = null;

	public function __construct() {

		//wat extra velden voor moderators.
		if (LoginModel::mag('P_LEDEN_MOD')) {
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
					if (in_array($value, $this->allowWeergave)) {
						$this->weergave = $value;
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
							$add = array_merge($add, array('S_LID', 'S_NOVIET', 'S_GASTLID'));
							continue;
						}
						if ($filter == 'OUDLEDEN') {
							$add = array_merge($add, array('S_OUDLID', 'S_ERELID'));
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
		$defaults = array();

		$zoekterm = MijnSqli::instance()->escape($zoekterm);

		if ($zoekterm == '*' OR trim($zoekterm) == '') {
			$query = '1 ';
		} elseif (preg_match('/^groep:([0-9]+|[a-z]+)$/i', $zoekterm)) { //leden van een groep
			$uids = array();
			try {
				//FIXME: $groep = new OldGroep(substr($zoekterm, 6));
				$uids = array_keys($groep->getLeden());
			} catch (\Exception $e) {
				//care.
			}
			$query = "uid IN('" . implode("','", $uids) . "') ";
		} elseif (preg_match('/^verticale:\w*$/', $zoekterm)) { //verticale, id, letter
			$v = substr($zoekterm, 10);
			if (strlen($v) > 1) {
				$result = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'));
				$query = array();
				foreach ($result as $v) {
					$query[] = 'verticale="' . $v->letter . '" ';
				}
				$query = '(' . implode(' OR ', $query) . ') ';
			} else {
				$verticale = VerticalenModel::get($v);
				if ($verticale) {
					$query = 'verticale="' . $verticale->letter . '" ';
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
				$query = $parts[0] . "='" . substr($parts[1], 1) . "'";
			} else {
				$query = $parts[0] . " LIKE '%" . $parts[1] . "%'";
			}
		} else { //als niets van hierboven toepasselijk is zoeken we in zo ongeveer alles
			$defaults[] = "voornaam LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "achternaam LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "CONCAT_WS(' ', voornaam, tussenvoegsel, achternaam) LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "CONCAT_WS(' ', voornaam, achternaam) LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "CONCAT_WS(' ', tussenvoegsel, achternaam) LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "CONCAT_WS(', ', achternaam, tussenvoegsel) LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "nickname LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "duckname LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "eetwens LIKE '%" . $zoekterm . "%' ";

			$defaults[] = "CONCAT_WS(' ', adres, postcode, woonplaats) LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "adres LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "postcode LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "woonplaats LIKE '%" . $zoekterm . "%' ";

			$defaults[] = "mobiel LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "telefoon LIKE '%" . $zoekterm . "%' ";

			$defaults[] = "studie LIKE '%" . $zoekterm . "%' ";
			$defaults[] = "email LIKE '%" . $zoekterm . "%' ";

			$query .= '( ' . implode(' OR ', $defaults) . ' )';
		}

		return $query . ' AND ';
	}

	/**
	 * Doe de zoektocht.
	 *
	 * @return Profiel[]
	 */
	public function search() {
		$db = MijnSqli::instance();

		$query = "SELECT uid FROM profielen WHERE ";

		if ($this->query != '') {
			$query .= $this->defaultSearch($this->query);
		}
		$query .= $this->getFilterSQL();
		$query .= ' ORDER BY ' . implode($this->sort) . ';';

		$this->sqlquery = $query;
		$result = $db->query2array($query);

		//De uid's omzetten naar Lid-objectjes
		$this->result = array();
		if (is_array($result)) {
			foreach ($result as $uid) {
				$profiel = ProfielModel::get($uid['uid']);
				if ($profiel) {
					$this->result[] = $profiel;
				}
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
		return 'LL' . ucfirst($this->weergave);
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
		$db = MijnSqli::instance();
		$filters = array();
		foreach ($this->filters as $key => $value) {
			if (is_array($value)) {
				$filters[] = $key . " IN ('" . implode("', '", $db->escape($value)) . "')";
			} else {
				$filters[] = $key . "='" . $db->escape($value) . "'";
			}
		}
		$return = implode(' AND ', $filters);
		if (strlen(trim($return)) == 0) {
			return '1';
		} else {
			return $return;
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

}
