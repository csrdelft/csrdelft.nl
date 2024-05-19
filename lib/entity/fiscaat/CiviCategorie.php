<?php

namespace CsrDelft\entity\fiscaat;

use CsrDelft\repository\fiscaat\CiviCategorieRepository;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CiviCategorie
 *
 * Een Product kan onderdeel van een categorie zijn. Deze categorie hoort ook bij een commissie.
 *
 * Als er veel gebruik gemaakt gaat worden van categorien en commissies moet hier uitgebreid worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
#[ORM\Table('civi_categorie')]
#[ORM\Index(name: 'cie', columns: ['cie'])]
#[ORM\Entity(repositoryClass: CiviCategorieRepository::class)]
class CiviCategorie implements DisplayEntity
{
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $type;
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 public $status;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string')] // TODO Dit is een CiviSaldoCommissieEnum
 public $cie;

	public function getBeschrijving(): string
	{
		return sprintf('%s (%s)', $this->type, $this->cie);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getWeergave(): string
	{
		return $this->getBeschrijving();
	}
}
