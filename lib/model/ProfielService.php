<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class ProfielService extends DependencyManager implements Service {

	const VELDEN = [
		'pasfoto', 'uid', 'naam', 'voorletters', 'voornaam', 'tussenvoegsel', 'achternaam', 'nickname', 'duckname', 'geslacht',
		'email', 'adres', 'telefoon', 'mobiel', 'linkedin', 'website', 'studie', 'status',
		'gebdatum', 'beroep', 'verticale', 'moot', 'lidjaar', 'kring', 'patroon', 'woonoord', 'bankrekening', 'eetwens'];

	const VELDEN_MOD = [
		'muziek', 'ontvangtcontactueel', 'kerk', 'lidafdatum',
		'echtgenoot', 'adresseringechtpaar', 'land', 'bankrekening', 'machtiging'];

	const ORDER_ALIAS = [
		'naam' => 'achternaam',
	];

	const DEFAULT_FILTER = [
		'status' => [LidStatus::Lid, LidStatus::Noviet, LidStatus::Gastlid]
	];

	/**
	 * @var ProfielModel
	 */
	private $profielModel;

	public function __construct(ProfielModel $profielModel) {
		$this->profielModel = $profielModel;
	}

	/**
	 * @param string $zoekterm
	 * @param string $zoekveld
	 * @param string $verticale
	 * @param string $sort
	 * @param string $zoekstatus
	 * @param int $limiet
	 * @return Profiel[]
	 */
	public function zoekLeden(string $zoekterm, string $zoekveld, string $verticale, string $sort, $zoekstatus = '', int $limiet = 0) {
		$containsZoekterm = sql_contains($zoekterm);
		$containsZonderSpatiesZoekterm = sql_contains(str_replace(' ', '', $zoekterm));
		$zoekfilterparams = [];
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if ($zoekveld == 'naam' AND !preg_match('/^\d{2}$/', $zoekterm)) {
			if (preg_match('/ /', trim($zoekterm))) {
				$zoekdelen = explode(' ', $zoekterm);
				$iZoekdelen = count($zoekdelen);
				if ($iZoekdelen == 2) {
					$zoekfilterparams[':voornaam'] = sql_contains($zoekdelen[0]);
					$zoekfilterparams[':achternaam'] = sql_contains($zoekdelen[1]);
					$zoekfilter = "( voornaam LIKE :voornaam AND achternaam LIKE :achternaam ) OR";
					$zoekfilter .= "( voornaam LIKE :zoekterm OR achternaam LIKE :containsZoekterm OR
                                    nickname LIKE :containsZoekterm OR uid LIKE :containsZoekterm )";
					$zoekfilterparams[':zoekterm'] = $zoekterm;
					$zoekfilterparams[':containsZoekterm'] = $zoekterm;
				} else {
					$zoekfilterparams[':voornaam'] = sql_contains($zoekdelen[0]);
					$zoekfilterparams[':achternaam'] = sql_contains($zoekdelen[$iZoekdelen - 1]);

					$zoekfilter = "( voornaam LIKE :voornaam AND achternaam LIKE :achternaam )";
				}
			} else {
				$zoekfilter = "
					voornaam LIKE :containsZoekterm OR achternaam LIKE :containsZoekterm OR
					nickname LIKE :containsZoekterm OR uid LIKE :containsZoekterm";
				$zoekfilterparams[':containsZoekterm']= sql_contains($zoekterm);
			}

			$zoekfilterparams[':naam'] = sql_contains($zoekterm);
			$zoekfilter .= " OR ( CONCAT(voornaam, \" \", tussenvoegsel, \" \", achternaam) LIKE :naam ) OR";
			$zoekfilter .= "( CONCAT(voornaam, \" \", achternaam) LIKE :naam )";
		} elseif ($zoekveld == 'adres') {
			$zoekfilter = "adres LIKE :containsZoekterm OR woonplaats LIKE :containsZoekterm OR
				postcode LIKE :containsZoekterm OR REPLACE(postcode, ' ', '') LIKE :containsZonderSpatiesZoekterm";
			$zoekfilterparams[':containsZoekterm'] = $zoekterm;
			$zoekfilterparams[':containsZonderSpatiesZoekterm'] = $containsZonderSpatiesZoekterm;
		} else {
			if (preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld == 'uid' OR $zoekveld == 'naam')) {
				//zoeken op lichtingen...
				$zoekfilter = "SUBSTRING(uid, 1, 2)=:zoekterm";
				$zoekfilterparams[':zoekterm'] = $zoekterm;

			} else {
				$zoekfilter = "{$zoekveld} LIKE :containsZoekterm";
				$zoekfilterparams[':containsZoekterm'] = $zoekterm;
			}
		}

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
		if (($verticale != 'alle')) {
			$mootfilter = 'AND verticale = :verticale ';
			$zoekfilterparams[':verticale'] = $verticale;
		} else {
			$mootfilter = '';
		}

		# is er een maximum aantal resultaten gewenst
		if ((int)$limiet > 0) {
			$limit = (int)$limiet;
		} else {
			$limit = null;
		}

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			$result = $this->profielModel->find("($zoekfilter) AND ($statusfilter) $mootfilter", $zoekfilterparams, null, $sort, $limit);

			return $result->fetchAll();
		}

		return [];
	}

	public function getAttributes() {
		return $this->profielModel->getAttributes();
	}

	public function count($zoekFilter) {
		return $this->find($zoekFilter, ['*'], [], -1, 0)->rowCount();
	}

	public function find($zoekFilter = null, $zoekVelden = [], $order = null, $limit = -1, $start = 0) {
		if ($zoekFilter == null || $zoekFilter == '*' || trim($zoekFilter) == '') {
			return $this->profielModel->find(null, [], null, $order, $limit, $start);
		} elseif (preg_match('/^groep:([0-9]+|[a-z]+)$/i', $zoekFilter)) { //leden van een groep
			$criteria_params = [];
			/*try {
				//FIXME: $groep = new OldGroep(substr($zoekterm, 6));
				$uids = array_keys($groep->getLeden());
			} catch (\Exception $e) {
				//care.
			}*/
			$criteria = "uid IN('" . implode("','", $criteria_params) . "') ";
		} elseif (preg_match('/^verticale:\w*$/', $zoekFilter)) { //verticale, id, letter
			$v = substr($zoekFilter, 10);
			if (strlen($v) > 1 || strtolower($v) == 'x') {
				$result = VerticalenModel::instance()->find('naam LIKE ?', array(sql_contains($v)))->fetchAll();
				$criteria_params = array_map(function ($verticale) { return $verticale->letter; }, $result);
				$criteria = 'verticale IN (' . implode(', ', array_fill(0, count($criteria_params), '?')) . ')';
			} else {
				$verticale = VerticalenModel::get($v);
				if ($verticale) {
					$criteria_params = [':verticale' => $verticale->letter];
				} else {
					$criteria_params = [':verticale' => ''];
				}

				$criteria = 'verticale = :verticale';
			}
		} elseif (preg_match('/^\d{2}$/', $zoekFilter)) { //lichting bij een string van 2 cijfers
			$criteria = 'RIGHT(lidjaar,2) = :search';
			$criteria_params = [':search' => $zoekFilter];
		} elseif (preg_match('/^lichting:\d\d(\d\d)?$/', $zoekFilter)) { //lichting op de explicite manier
			$lichting = substr($zoekFilter, 9);

			$criteria_params = [':lichting' => $lichting];

			if (strlen($lichting) == 4) {
				$criteria = 'lidjaar = :lichting';
			} else {
				$criteria = 'RIGHT(lidjaar, 2) = :lichting';
			}
		} elseif (preg_match('/^[a-z0-9][0-9]{3}$/', $zoekFilter)) { //uid's is ook niet zo moeilijk.
			$criteria = 'uid = :search';
			$criteria_params = [':search' => $zoekFilter];
		} elseif (preg_match('/^([a-z0-9][0-9]{3} ?,? ?)*([a-z0-9][0-9]{3})$/', $zoekFilter)) {
			//meerdere uid's gescheiden door komma's.
			//explode en trim() elke waarde van de array.
			$criteria_params = array_map('trim', explode(',', $zoekFilter));
			$criteria = 'uid IN(' . implode(', ', array_fill(0, count($criteria_params), '?')) . ') ';
		} elseif (preg_match('/^(' . implode('|', $zoekVelden) . '):=?([a-z0-9\-_])+$/i', $zoekFilter)) {
			//Zoeken in de velden van $this->allowVelden. Zoektermen met 'veld:' ervoor.
			//met 'veld:=<zoekterm> wordt exact gezocht.
			$parts = explode(':', $zoekFilter);

			if ($parts[1][0] == '=') {
				$criteria = "{$parts[0]} = :search";
				$criteria_params = [':search' => $parts[1]];
			} else {
				$criteria = "{$parts[0]} LIKE :search";
				$criteria_params = [':search' => sql_contains($parts[1])];
			}
		} else {
			$criteria = <<<'QUERY'
voornaam LIKE :search 
OR achternaam LIKE :search 
OR CONCAT_WS(' ', voornaam, tussenvoegsel, achternaam) LIKE :search 
OR CONCAT_WS{' ', voornaam, achternaam) LIKE :search
OR CONCAT_WS(' ', tussenvoegsel, achternaam) LIKE :search
OR CONCAT_WS(' ', achternaam, tussenvoegsel) LIKE :search
OR nickname LIKE :search
OR duckname LIKE :search
OR eetwens LIKE :search
OR CONCAT_WS(' ', adres, postcode, woonplaats) LIKE :search
OR adres LIKE :search
OR postcode LIKE :search
OR woonplaats LIKE :search
OR mobiel LIKE :search
OR telefoon LIKE :search
OR studie LIKE :search
OR email LIKE :search
QUERY;
			$criteria_params = [':search' => sql_contains($zoekFilter)];
		}

		return $this->profielModel->find($criteria, $criteria_params, null, $order, $limit, $start);
	}
}
