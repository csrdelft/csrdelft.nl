<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\model\ProfielModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class EetplanBekenden
 * @package CsrDelft\model\entity\eetplan
 * @ORM\Entity(repositoryClass="CsrDelft\repository\eetplan\EetplanBekendenRepository")
 */
class EetplanBekenden {
	/**
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 * @var string
	 */
	public $uid1;
	/**
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 * @var string
	 */
	public $uid2;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $opmerking;

	public function getNoviet1() {
		return ProfielModel::instance()->find('uid = ?', array($this->uid1))->fetch();
	}

	public function getNoviet2() {
		return ProfielModel::instance()->find('uid = ?', array($this->uid2))->fetch();
	}
}
