<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\MijnSqli;
use CsrDelft\model\bibliotheek\BiebBeschrijving;
use CsrDelft\model\bibliotheek\BiebRubriek;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Boek extends PersistentEntity {

	public $id;   //boekId
	public $titel;   //String
	public $titel_imp;
	public $auteur;   //String Auteur
	public $auteur_imp;
	public $uitgavejaar;
	public $uitgeverij;
	public $paginas;
	public $taal = 'Nederlands';
	public $isbn;
	public $code;
	public $categorie_id;
	protected static $table_name = 'biebboek';
	protected $beschrijvingen;


	public function getId() {
		return $this->id;
	}

	public function getTitel() {
		return $this->titel_imp;
	}

	public function getUitgavejaar() {
		return $this->uitgavejaar;
	}

	public function getUitgeverij() {
		return $this->uitgeverij;
	}

	public function getPaginas() {
		return $this->paginas;
	}

	public function getTaal() {
		return $this->taal;
	}

	public function getISBN() {
		return $this->isbn;
	}

	public function getCode() {
		return $this->code;
	}

	public function getAuteur() {
		return $this->auteur_imp;
	}

	public function getRubriek() {
		return new BiebRubriek($this->categorie_id);
	}
	/**
	 * Controleert rechten voor wijderactie
	 *
	 * @return  bool
	 *    boek mag alleen door admins verwijdert worden
	 */
	public function magVerwijderen() {
		return LoginModel::mag('commissie:BASFCie,P_BIEB_MOD,P_ADMIN');
	}

	/**
	 * Controleert rechten voor bewerkactie
	 *
	 * @return  bool
	 *    boek mag alleen door admins of door eigenaar v.e. exemplaar bewerkt worden
	 */
	public function magBewerken() {
		return LoginModel::mag('P_BIEB_EDIT') OR $this->isEigenaar() OR $this->magVerwijderen();
	}

	/**
	 * Iedereen met extra rechten en zij met BIEB_READ mogen
	 */
	public function magBekijken() {
		return LoginModel::mag('P_BIEB_READ') OR $this->magBewerken();
	}

	/**
	 * Controleert of ingelogd eigenaar is van boek/exemplaar
	 *  - BASFCieleden zijn eigenaar van boeken van de bibliotheek
	 *
	 * @param null|int geen of $exemplaarid integer
	 * @return bool true
	 *        of ingelogd eigenaar is v.e. exemplaar van het boek
	 *        of van het specifieke exemplaar als exemplaarid is gegeven.
	 *      false
	 *        geen geen resultaat of niet de eigenaar
	 */
	public function isEigenaar($exemplaarid = null) {
		$eigenaars = $this->getEigenaars($exemplaarid);
		foreach ($eigenaars as $eigenaar) {
			if ($eigenaar == LoginModel::getUid()) {
				return true;
			} elseif ($eigenaar == 'x222' AND LoginModel::mag('R_BASF')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns an array of eigenaaruids van boek of exemplaar
	 *
	 * @param null|int $exemplaarid
	 * @return array
	 */
	public function getEigenaars($exemplaarid = null) {
		$db = MijnSqli::instance();
		if ($exemplaarid == null) {
			$where = "WHERE boek_id =" . (int)$this->getId();
		} else {
			$where = "WHERE id =" . (int)$exemplaarid;
		}
		$qEigenaar = "
			SELECT eigenaar_uid
			FROM  `biebexemplaar` 
			" . $where . ";";
		$result = $db->query($qEigenaar);

		$eigenaars = array();
		if ($db->numRows($result) > 0) {
			while ($eigenaar = $db->next($result)) {
				$eigenaars[] = $eigenaar['eigenaar_uid'];
			}
		}
		return $eigenaars;
	}


	public function isBiebboek($exemplaarid = null) {
		$eigenaars = $this->getEigenaars($exemplaarid);
		foreach ($eigenaars as $eigenaar) {
			if ($eigenaar == 'x222') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Geeft alle exemplaren van dit boek
	 *
	 * @return array met exemplaren
	 */
	public function getExemplaren() {
		if ($this->exemplaren === null) {
			$this->loadExemplaren();
		}
		return $this->exemplaren;
	}


	/**
	 * Laad exemplaren van dit boek in Boek
	 *
	 * @return bool|int
	 */
	public function loadExemplaren() {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, eigenaar_uid, opmerking, uitgeleend_uid, toegevoegd, status, uitleendatum
			FROM biebexemplaar
			WHERE boek_id=" . (int)$this->getId() . "
			ORDER BY toegevoegd;";
		$result = $db->query($query);

		if ($db->numRows($result) > 0) {
			while ($exemplaar = $db->next($result)) {
				$this->exemplaren[$exemplaar['id']] = $exemplaar;
			}
		} else {
			$this->error .= $db->error();
			return false;
		}
		return $db->numRows($result);
	}

	/**
	 * laad beschrijvingen van dit boek, inclusief Beschrijving(0) indien nodig.
	 */
	protected function loadBeschrijvingen() {
		$db = MijnSqli::instance();
		$query = "
			SELECT id, boek_id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
			FROM biebbeschrijving
			WHERE boek_id=" . (int)$this->getId() . "
			ORDER BY toegevoegd;";
		$result = $db->query($query);
		if ($db->numRows($result) > 0) {
			while ($beschrijving = $db->next($result)) {
				$this->beschrijvingen[$beschrijving['id']] = new BiebBeschrijving($beschrijving);
			}
		} else {
			$this->error .= $db->error();
		}

	}

	/**
	 * Geeft array met beschrijvingen van dit boek
	 *
	 * @return array Beschrijving[]
	 */
	public function getBeschrijvingen() {
		if ($this->beschrijvingen === null) {
			$this->loadBeschrijvingen();
		}
		return $this->beschrijvingen;
	}

	/**
	 * Aantal beschrijvingen
	 *
	 * @return int
	 */
	public function countBeschrijvingen() {
		return count($this->getBeschrijvingen());
	}

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'auteur' => [T::String, false],
		'auteur_imp' => [T::String, true],
		'auteur_id' => [T::Integer, false],
		'titel' => [T::String, false],
		'titel_imp' => [T::String, true],
		'taal' => [T::String, false],
		'taal_imp' => [T::String, true],
		'isbn' => [T::String, false],
		'categorie_id' => [T::Integer, false],
		'paginas' => [T::Integer, false],
		'paginas_imp' => [T::Integer, true],
		'uitgavejaar' => [T::Integer, false],
		'uitgavejaar_imp' => [T::Integer, true],
		'uitgeverij' => [T::String, false],
		'uitgeverij_imp' => [T::String, false],
		'code' => [T::String, false],
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}