<?php

namespace CsrDelft\entity\eetplan;

use CsrDelft\entity\profiel\Profiel;
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
	 * @var Profiel
	 * @ORM\OneToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid1", referencedColumnName="uid")
	 */
	public $noviet1;
	/**
	 * @var Profiel
	 * @ORM\OneToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid2", referencedColumnName="uid")
	 */
	public $noviet2;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	public $opmerking;
}
