<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\repository\declaratie\DeclaratieWachtrijRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeclaratieWachtrijRepository::class)]
class DeclaratieWachtrij
{
	#[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 private $id;

	#[ORM\Column(type: 'string', length: 255)]
 private $naam;

	#[ORM\Column(type: 'string', length: 255)]
 private $rechten;

	#[ORM\Column(type: 'integer')]
 private $positie;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
 private $email;

	#[ORM\Column(type: 'string', length: 2, nullable: true)]
 private $prefix;

	#[ORM\OneToMany(targetEntity: DeclaratieCategorie::class, mappedBy: 'wachtrij')]
 private $categorieen;

	public function __construct()
	{
		$this->categorieen = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNaam(): string
	{
		return $this->naam;
	}

	public function setNaam(string $naam): self
	{
		$this->naam = $naam;

		return $this;
	}

	public function getRechten(): string
	{
		return $this->rechten;
	}

	public function setRechten(string $rechten): self
	{
		$this->rechten = $rechten;

		return $this;
	}

	public function getPositie(): int
	{
		return $this->positie;
	}

	public function setPositie(int $positie): self
	{
		$this->positie = $positie;

		return $this;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(?string $email): self
	{
		$this->email = $email;
		return $this;
	}

	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	public function setPrefix(?string $prefix): self
	{
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * @return Collection|DeclaratieCategorie[]
	 */
	public function getCategorieen(): Collection
	{
		return $this->categorieen;
	}

	public function addCategorie(DeclaratieCategorie $categorie): self
	{
		if (!$this->categorieen->contains($categorie)) {
			$this->categorieen[] = $categorie;
			$categorie->setWachtrij($this);
		}

		return $this;
	}

	public function removeCategorie(DeclaratieCategorie $categorie): self
	{
		if ($this->categorieen->contains($categorie)) {
			$this->categorieen->removeElement($categorie);
			// set the owning side to null (unless already changed)
			if ($categorie->getWachtrij() === $this) {
				$categorie->setWachtrij(null);
			}
		}

		return $this;
	}

	public function magBeoordelen(): bool
	{
		return LoginService::mag($this->rechten) || Declaratie::isFiscus();
	}
}
