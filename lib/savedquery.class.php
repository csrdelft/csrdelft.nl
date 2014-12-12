<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------

class SavedQuery {

	private $queryID;
	private $beschrijving;
	private $permissie = 'P_ADMIN';
	private $result = null;
	private $resultCount = 0;

	public function __construct($id) {
		$this->queryID = (int) $id;
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

class SavedQueryContent implements View {

	/**
	 * Saved query
	 * @var SavedQuery
	 */
	private $sq;

	public function __construct(SavedQuery $sq = null) {
		$this->sq = $sq;
	}

	public function getModel() {
		return $this->sq;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Opgeslagen query\'s';
	}

	public static function render_header($name) {
		switch ($name) {
			case 'uid_naam': return 'Naam';
				break;
			case 'groep_naam': return 'Groep';
				break;
			case 'onderwerp_link': return 'Onderwerp';
				break;
			case 'med_link': return 'Mededeling';
				break;
			default:
				if (substr($name, 0, 10) == 'groep_naam') {
					return substr($name, 11);
				}
		}
		return $name;
	}

	public static function render_field($name, $contents) {
		if ($name == 'uid_naam') {
			return Lid::naamLink($contents, 'full', 'link');
		} elseif ($name == 'onderwerp_link') { //link naar het forum.
			return '<a href="/forum/onderwerp/' . $contents . '">' . $contents . '</a>';
		} elseif (substr($name, 0, 10) == 'groep_naam' AND $contents != '') {
			require_once 'groepen/groep.class.php';
			return OldGroep::ids2links($contents, '<br />');
		} elseif ($name == 'med_link') { //link naar een mededeling.
			return '<a href="/mededelingen/' . $contents . '">' . $contents . '</a>';
		}

		return htmlspecialchars($contents);
	}

	public function render_queryResult() {
		if ($this->sq->hasResult()) {
			$sq = $this->sq;
			$id = 'query-' . time();
			$return = $sq->getBeschrijving() . ' (' . $sq->count() . ' regels)<br /><table class="query_table" id="' . $id . '">';

			$return .= '<thead><tr>';
			foreach ($sq->getHeaders() as $kopje) {
				$return .= '<th>' . self::render_header($kopje) . '</th>';
			}
			$return .= '</tr></thead><tbody>';

			foreach ($sq->getResult() as $rij) {
				$return .= '<tr>';
				foreach ($rij as $key => $veld) {
					$return .= '<td>' . self::render_field($key, $veld) . '</td>';
				}
				$return .= '</tr>';
			}
			$return .= '</tbody></table><a class="btn clear-right vergroot" data-vergroot="#' . $id . ' tbody" title="Vergroot de lijst">&uarr;&darr;</a>';
		} else {
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return = 'Query (' . $this->sq->getID() . ') bestaat niet, geeft een fout, of u heeft niet voldoende rechten.';
		}
		return $return;
	}

	public function getQueryselector() {
		//als er een query ingeladen is, die highlighten
		$id = $this->sq instanceof SavedQuery ? $this->sq->getID() : 0;

		$return = '<a class="btn" href="#" onclick="$(\'#sqSelector\').toggle();">Laat queryselector zien.</a>';
		$return .= '<div id="sqSelector" ';
		if ($id != 0) {
			$return .= 'class="verborgen"';
		}
		$return .= '>';
		$current = '';
		foreach (SavedQuery::getQueries() as $query) {
			if ($current != $query['categorie']) {
				if ($current != '') {
					$return .= '</ul></div>';
				}
				$return .= '<div class="sqCategorie "><span class="dikgedrukt">' . $query['categorie'] . '</span><ul>';
				$current = $query['categorie'];
			}
			$return .= '<li><a href="query.php?id=' . $query['ID'] . '">';
			if ($id == $query['ID']) {
				$return .= '<span class="cursief">';
			}
			$return.=htmlspecialchars($query['beschrijving']);
			if ($id == $query['ID']) {
				$return .= '</span>';
			}
			$return .= '</a></li>';
		}
		$return .= '</ul></div></div><div class="clear"></div>';
		return $return;
	}

	public function view() {
		echo '<h1>' . $this->getTitel() . '</h1>';
		echo $this->getQueryselector();

		//render query if selected and allowed
		if ($this->sq != null && $this->sq->magBekijken()) {
			echo $this->render_queryResult();
		}
	}

}
