<?php


namespace CsrDelft\model\entity\bibliotheek;


use CsrDelft\model\bibliotheek\BiebRubriekModel;
use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Boek extends PersistentEntity {

	public $id;   //boekId
	public $titel;   //String
	public $auteur;   //String Auteur
	public $uitgavejaar;
	public $uitgeverij;
	public $paginas;
	public $taal = 'Nederlands';
	public $isbn;
	public $code;
	public $categorie_id;
	public $auteur_id = 0;
	protected static $table_name = 'biebboek';
	protected $beschrijvingen;


	public function getId() {
		return $this->id;
	}

	public function getTitel() {
		return $this->titel;
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
		return $this->auteur;
	}

	public function getRubriek() {
		return $this->categorie_id != null ? BiebRubriekModel::get($this->categorie_id) : null;

	}
	public function getStatus() {
		return "";
	}

	//url naar dit boek
	public function getUrl() {
		return '/bibliotheek/boek/' . $this->getId();
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
	public function isEigenaar($uid = null) {
		$exemplaren = $this->getExemplaren();
		foreach ($exemplaren as $exemplaar) {
			if($uid != null) {
				if($uid == $exemplaar->eigenaar_uid) {
					return true;
				}
			}
			else if ($exemplaar->isEigenaar()) {
				return true;
			}
		}
		return false;
	}

	public function isBiebBoek() {
		$exemplaren = $this->getExemplaren();
		foreach ($exemplaren as $exemplaar) {
			if ($exemplaar->isBiebBoek()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Geeft alle exemplaren van dit boek
	 *
	 * @return BoekExemplaar[]
	 */
	public function getExemplaren() : array {
		return BoekExemplaarModel::getExemplaren($this)->fetchAll();
	}




	/**
	 * Geeft array met beschrijvingen van dit boek
	 *
	 * @return array Beschrijving[]
	 */
	public function getBeschrijvingen() {
		return BoekRecensieModel::getVoorBoek($this->id);
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
		'id' => [T::Integer, false, 'auto_increment'],
		'auteur' => [T::String, false],
		'auteur_id' => [T::Integer, false],
		'titel' => [T::String, false],
		'taal' => [T::String, false],
		'isbn' => [T::String, false],
		'categorie_id' => [T::Integer, false],
		'paginas' => [T::Integer, false],
		'uitgavejaar' => [T::Integer, false],
		'uitgeverij' => [T::String, false],
		'code' => [T::String, false],
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];

	/**
	 * @return BoekRecensie[]
	 */
	public function getRecensies() {
		return BoekRecensieModel::instance()->find('boek_id = ?', [$this->id])->fetchAll();
	}
}
