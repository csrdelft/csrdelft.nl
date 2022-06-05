<?php

namespace CsrDelft\entity\bibliotheek;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BiebAuteur
 * @package CsrDelft\entity\bibliotheek
 * @ORM\Entity()
 * @ORM\Table("biebauteur")
 */
class BiebAuteur
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=100, options={"default"=""})
	 */
	public $auteur;
}
