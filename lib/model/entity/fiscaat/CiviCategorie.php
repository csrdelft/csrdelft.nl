<?php

namespace CsrDelft\model\entity\fiscaat;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CiviCategorie
 *
 * Een Product kan onderdeel van een categorie zijn. Deze categorie hoort ook bij een commissie.
 *
 * Als er veel gebruik gemaakt gaat worden van categorien en commissies moet hier uitgebreid worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fiscaat\CiviCategorieRepository")
 * @ORM\Table("CiviCategorie")
 */
class CiviCategorie {
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
	public $type;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 */
	public $status;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 * TODO Dit is een CiviSaldoCommissieEnum
	 */
	public $cie;

	public function getBeschrijving() {
		return sprintf('%s (%s)', $this->type, $this->cie);
	}
}
