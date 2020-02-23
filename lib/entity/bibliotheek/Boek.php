<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\model\security\LoginModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\bibliotheek
 * @ORM\Entity(repositoryClass="CsrDelft\repository\bibliotheek\BoekRepository")
 * @ORM\Table("biebboek")
 */
class Boek {

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $titel;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $auteur;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $uitgavejaar;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $uitgeverij;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $paginas;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $taal = 'Nederlands';
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $isbn;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $code;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $categorie_id;

	/**
	 * @var BoekRecensie[]
	 * @ORM\OneToMany(targetEntity="BoekRecensie", mappedBy="boek")
	 */
	protected $recensies;

	/**
	 * @var BoekExemplaar[]
	 * @ORM\OneToMany(targetEntity="BoekExemplaar", mappedBy="boek")
	 */
	protected $exemplaren;

	/**
	 * @var BiebRubriek|null
	 * @ORM\ManyToOne(targetEntity="BiebRubriek")
	 * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id")
	 */
	protected $categorie;

	public function getRubriek() {
		return $this->categorie;
	}

	public function setCategorie(BiebRubriek $biebRubriek) {
		$this->categorie = $biebRubriek;
	}

	public function getStatus() {
		return "";
	}

	public function getUrl() {
		return '/bibliotheek/boek/' . $this->id;
	}

	/**
	 * Iedereen met extra rechten en zij met BIEB_READ mogen
	 */
	public function magBekijken() {
		return LoginModel::mag(P_BIEB_READ) || $this->magBewerken();
	}

	/**
	 * Controleert rechten voor bewerkactie
	 *
	 * @return  bool
	 *    boek mag alleen door admins of door eigenaar v.e. exemplaar bewerkt worden
	 */
	public function magBewerken() {
		return LoginModel::mag(P_BIEB_EDIT) || $this->isEigenaar() || $this->magVerwijderen();
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
		foreach ($this->getExemplaren() as $exemplaar) {
			if ($uid != null) {
				if ($uid == $exemplaar->eigenaar_uid) {
					return true;
				}
			} else if ($exemplaar->isEigenaar()) {
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
	public function getExemplaren() {
		return $this->exemplaren ?? [];
	}

	/**
	 * Controleert rechten voor wijderactie
	 *
	 * @return  bool
	 *    boek mag alleen door admins verwijdert worden
	 */
	public function magVerwijderen() {
		return LoginModel::mag('commissie:BASFCie,' . P_BIEB_MOD . ',' . P_ADMIN);
	}

	public function isBiebBoek() {
		foreach ($this->getExemplaren() as $exemplaar) {
			if ($exemplaar->isBiebBoek()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return BoekRecensie[]
	 */
	public function getRecensies() {
		return $this->recensies ?? [];
	}
}
