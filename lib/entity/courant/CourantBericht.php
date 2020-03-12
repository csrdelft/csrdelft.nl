<?php


namespace CsrDelft\entity\courant;


use CsrDelft\model\security\LoginModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CourantBericht
 * @package CsrDelft\entity\courant
 * @ORM\Entity(repositoryClass="CsrDelft\repository\CourantBerichtRepository")
 * @ORM\Table("courantbericht")
 */
class CourantBericht {
	/**
	 * @var integer
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
	public $cat;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $bericht;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $volgorde;
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $uid;
	/**
	 * @var DateTime
	 * @ORM\Column(type="datetime", name="datumTijd")
	 */
	public $datumTijd;

	public function setVolgorde() {
		$this->volgorde = [
			null => null,
			'voorwoord' => 0,
			'bestuur' => 1,
			'csr' => 2,
			'overig' => 3,
			'sponsor' => 4,
		][$this->cat];
	}

	public function magBeheren() {
		return LoginModel::mag(P_MAIL_COMPOSE) OR LoginModel::mag($this->uid);
	}
}
