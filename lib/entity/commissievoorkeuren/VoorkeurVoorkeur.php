<?php

namespace CsrDelft\entity\commissievoorkeuren;

use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\AccessService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;

/**
 * Class VoorkeurVoorkeur
 * @package CsrDelft\model\entity\commissievoorkeuren
 */
#[ORM\Entity(repositoryClass: CommissieVoorkeurRepository::class)]
class VoorkeurVoorkeur
{
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[ORM\Id]
 public $uid;

	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 public $cid;

	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 public $voorkeur;

	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime')]
 public $timestamp;

	#[ORM\PreUpdate]
	public function setTimestamp()
	{
		$this->timestamp = new DateTimeImmutable();
	}

	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $profiel;

	/**
  * @var VoorkeurCommissie
  */
 #[ORM\JoinColumn(name: 'cid')]
 #[ORM\ManyToOne(targetEntity: VoorkeurCommissie::class)]
 public $commissie;

	public function getCommissieNaam()
	{
		return $this->commissie->naam;
	}

	public function getCategorieNaam()
	{
		return $this->commissie->categorie->naam;
	}

	/**
	 * cid is onderdeel van primary key en moet dus gezet zijn bij saven.
	 *
	 * @param VoorkeurCommissie $commissie
	 */
	public function setCommissie(VoorkeurCommissie $commissie)
	{
		$this->commissie = $commissie;
		$this->cid = $commissie->id;
	}

	/**
	 * uid is onderdeel van primary key en moet dus gezet zijn bij saven.
	 *
	 * @param Profiel $profiel
	 */
	public function setProfiel(Profiel $profiel)
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->uid;
	}

	public function heeftGedaan()
	{
		return ContainerFacade::getContainer()
			->get(AccessService::class)
			->isUserGranted(
				$this->profiel->account,
				'commissie:' .
					$this->commissie->naam .
					',commissie:' .
					$this->commissie->naam .
					':ot'
			);
	}

	public function getVoorkeurTekst(): string
	{
		return ['', 'nee', 'ja', 'misschien'][$this->voorkeur];
	}
}
