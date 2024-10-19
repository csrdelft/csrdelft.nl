<?php

namespace CsrDelft\entity\commissievoorkeuren;

use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieCategorieRepository;
use VoorkeurCommissie;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurCommissieCategorie
 * @package CsrDelft\model\entity\commissievoorkeuren
 */
#[ORM\Entity(repositoryClass: VoorkeurCommissieCategorieRepository::class)]
class VoorkeurCommissieCategorie
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;

	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $naam;

	/**
	 * @var VoorkeurCommissie[]
	 */
	#[
		ORM\OneToMany(targetEntity: VoorkeurCommissie::class, mappedBy: 'categorie')
	]
	public $commissies;
}
