<?php

namespace CsrDelft;

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------

use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\View;

class SavedQuery {

	private $queryID;
	private $beschrijving;
	private $permissie = 'P_ADMIN';
	private $result = null;
	private $resultCount = 0;

	public function __construct($id) {
		$this->queryID = (int)$id;
		$this->load();
	}

	private function load() {
		$db = MijnSqli::instance();
		//query ophalen
		$selectQuery = "
			SELECT
				savedquery, beschrijving, permissie
			FROM
				savedquery
			WHERE
				ID=" . $this->queryID . "
			LIMIT 1;";
		$result = $db->query($selectQuery);

		if ($result !== false AND $db->numRows($result) == 1) {
			$querydata = $db->next($result);

			if ($this->magWeergeven($querydata['permissie'])) {
				//beschrijving opslaan
				$this->beschrijving = $querydata['beschrijving'];
				$this->permissie = $querydata['permissie'];

				//query nog uitvoeren...
				$queryResult = $db->query($querydata['savedquery']);

				if ($queryResult !== false) {
					if ($db->numRows($queryResult) == 0) {
						$this->result[] = array('Leeg resultaatset' => 'Query leverde geen resultaten terug.');
					} else {
						$this->result = $db->result2array($queryResult);
						$this->resultCount = count($this->result);
					}
				} elseif (LoginModel::mag('P_ADMIN')) {
					$this->result[] = array('mysqli_error' => $db->error());
				}
			}
		}
	}

	public function getID() {
		return $this->queryID;
	}

	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function getHeaders() {
		if ($this->hasResult()) {
			return array_keys($this->result[0]);
		} else {
			return array();
		}
	}

	public function hasResult() {
		return is_array($this->result);
	}

	public function getResult() {
		return $this->result;
	}

	public function count() {
		return $this->resultCount;
	}

	//Query's mogen worden weergegeven als de permissiestring toegelaten wordt door
	//Lid::mag()' of als gebruiker P_ADMIN heeft.
	public static function magWeergeven($permissie) {
		return LoginModel::mag($permissie) OR LoginModel::mag('P_ADMIN');
	}

	public function magBekijken() {
		return $this->magWeergeven($this->permissie);
	}

	//geef een array terug met de query's die de huidige gebruiker mag bekijken.
	static public function getQueries() {
		$db = MijnSqli::instance();
		$selectQuery = "
			SELECT
				ID, beschrijving, permissie, categorie
			FROM
				savedquery
			ORDER BY categorie, beschrijving;";
		$result = $db->query($selectQuery);
		$return = array();
		while ($data = $db->next($result)) {
			if (self::magWeergeven($data['permissie'])) {
				$return[] = $data;
			}
		}
		return $return;
	}

}
