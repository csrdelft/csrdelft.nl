<?php


namespace CsrDelft\entity\bibliotheek;


use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\model\entity\bibliotheek
 * @ORM\Entity(repositoryClass="CsrDelft\repository\bibliotheek\BoekExemplaarRepository")
 * @ORM\Table("biebexemplaar")
 */
class BoekExemplaar extends PersistentEntity {

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $boek_id;
	/**
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $eigenaar_uid;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $opmerking;

	/**
	 * @var string
	 * @ORM\Column(type="stringkey", nullable=true)
	 */
	public $uitgeleend_uid;

	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $toegevoegd;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $status = 'beschikbaar';

	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $uitleendatum;
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $leningen;

	/**
	 * @var Boek
	 * @ORM\ManyToOne(targetEntity="Boek", inversedBy="exemplaren")
	 * @ORM\JoinColumn(name="boek_id", referencedColumnName="id")
	 */
	public $boek;

	public function isBiebBoek() : bool {
		return $this->eigenaar_uid == 'x222';
	}

	public function isEigenaar() : bool {
		if ($this->eigenaar_uid == LoginModel::getUid()) {
			return true;
		} elseif ($this->isBiebBoek() && LoginModel::mag(P_BIEB_MOD)) {
			return true;
		}
		return false;
	}

	public function magBewerken() : bool {
		return $this->isEigenaar();
	}
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return Boek
	 */
	public function getBoek() {
		return $this->boek;
	}

	public function magBekijken() {
		return LoginModel::mag(P_BIEB_READ) || $this->magBewerken();
	}

	public function isBeschikbaar() {
		return $this->getStatus() == 'beschikbaar';
	}

	public function kanLenen(string $uid) {
		return $this->eigenaar_uid != $uid && $this->isBeschikbaar();
	}

	public function isUitgeleend() {
		return $this->status == 'uitgeleend';
	}

	public function isTeruggegeven() {
		return $this->status == 'teruggegeven';
	}

	public function isVermist() {
		return $this->status == 'vermist';
	}
}
