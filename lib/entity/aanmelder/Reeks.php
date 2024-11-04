<?php

namespace CsrDelft\entity\aanmelder;

use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\repository\aanmelder\ReeksRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ReeksRepository::class)]
#[ORM\Table(name: 'aanmelder_reeks')]
class Reeks extends ActiviteitEigenschappen implements DataTableEntry
{
	#[Serializer\Groups(['datatable'])]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	public $id;

	#[Serializer\Groups(['datatable'])]
	#[ORM\Column(type: 'string', length: 255)]
	private $naam;

	#[ORM\Column(type: 'string', length: 255)]
	private $rechtenAanmaken;

	#[
		ORM\OneToMany(
			targetEntity: AanmeldActiviteit::class,
			mappedBy: 'reeks',
			orphanRemoval: true
		)
	]
	#[ORM\OrderBy(['start' => 'ASC', 'einde' => 'ASC'])]
	private $activiteiten;

	public function __construct()
	{
		$this->activiteiten = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNaam(): ?string
	{
		return $this->naam;
	}

	public function getRechtenAanmaken(): ?string
	{
		return $this->rechtenAanmaken;
	}

	public function getActiviteiten(): Collection
	{
		return $this->activiteiten;
	}

	public function magActiviteitenBeheren(): bool
	{
		return self::magAanmaken() ||
			LoginService::mag($this->getRechtenAanmaken());
	}

	public static function magAanmaken(): bool
	{
		return LoginService::mag(P_ADMIN);
	}
}
