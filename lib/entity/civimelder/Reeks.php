<?php

namespace CsrDelft\entity\civimelder;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\repository\civimelder\ReeksRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ReeksRepository::class)
 * @ORM\Table(name="civimelder_reeks")
 */
class Reeks extends ActiviteitEigenschappen implements DataTableEntry {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Serializer\Groups({"datatable"})
	 */
	private $naam;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $rechtenAanmaken;

	/**
	 * @ORM\OneToMany(targetEntity=Activiteit::class, mappedBy="reeks", orphanRemoval=true)
	 * @ORM\OrderBy({"start" = "ASC", "einde" = "ASC"})
	 */
	private $activiteiten;

	public function __construct() {
		$this->activiteiten = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getNaam(): ?string {
		return $this->naam;
	}

	public function setNaam(string $naam): self {
		$this->naam = $naam;

		return $this;
	}

	public function getRechtenAanmaken(): ?string {
		return $this->rechtenAanmaken;
	}

	public function setRechtenAanmaken(string $rechtenAanmaken): self {
		$this->rechtenAanmaken = $rechtenAanmaken;

		return $this;
	}

	/**
	 * @return Collection|Activiteit[]
	 */
	public function getActiviteiten(): Collection {
		return $this->activiteiten;
	}

	public function addActiviteiten(Activiteit $activiteiten): self {
		if (!$this->activiteiten->contains($activiteiten)) {
			$this->activiteiten[] = $activiteiten;
			$activiteiten->setReeks($this);
		}

		return $this;
	}

	public function removeActiviteiten(Activiteit $activiteiten): self {
		if ($this->activiteiten->contains($activiteiten)) {
			$this->activiteiten->removeElement($activiteiten);
			// set the owning side to null (unless already changed)
			if ($activiteiten->getReeks() === $this) {
				$activiteiten->setReeks(null);
			}
		}

		return $this;
	}

	public function magActiviteitenBeheren(): bool {
		return self::magAanmaken() || LoginService::mag($this->getRechtenAanmaken());
	}

	public static function magAanmaken(): bool {
		return LoginService::mag(P_ADMIN);
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("detailSource")
	 */
	public function getDetailSource() {
		return '/civimelder/beheer/activiteiten/' . $this->id;
	}
}
