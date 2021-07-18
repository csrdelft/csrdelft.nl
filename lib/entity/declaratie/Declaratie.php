<?php

namespace CsrDelft\entity\declaratie;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\declaratie\DeclaratieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity(repositoryClass=DeclaratieRepository::class)
 */
class Declaratie
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(nullable=false, referencedColumnName="uid")
	 */
	private $indiener;

	/**
	 * @ORM\ManyToOne(targetEntity=DeclaratieCategorie::class, inversedBy="declaraties")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $categorie;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $omschrijving;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $csrPas;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $rekening;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $naam;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $opmerkingen;

	/**
	 * @ORM\Column(type="float")
	 */
	private $totaal;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(referencedColumnName="uid")
	 */
	private $beoordelaar;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $nummer;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $ingediend = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $beoordeeld = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $goedgekeurd = false;

	/**
	 * @ORM\OneToMany(targetEntity=DeclaratieBon::class, mappedBy="declaratie")
	 */
	private $bonnen;

	public function __construct()
	{
		$this->bonnen = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getIndiener(): Profiel
	{
		return $this->indiener;
	}

	public function setIndiener(Profiel $indiener): self
	{
		$this->indiener = $indiener;

		return $this;
	}

	public function getCategorie(): ?DeclaratieCategorie
	{
		return $this->categorie;
	}

	public function setCategorie(?DeclaratieCategorie $categorie): self
	{
		$this->categorie = $categorie;

		return $this;
	}

	public function getOmschrijving(): ?string
	{
		return $this->omschrijving;
	}

	public function setOmschrijving(?string $omschrijving): self
	{
		$this->omschrijving = $omschrijving;

		return $this;
	}

	public function getCsrPas(): bool
	{
		return $this->csrPas;
	}

	public function setCsrPas(bool $csrPas): self
	{
		$this->csrPas = $csrPas;

		return $this;
	}

	public function getRekening(): ?string
	{
		return $this->rekening;
	}

	public function setRekening(?string $rekening): self
	{
		$this->rekening = $rekening;

		return $this;
	}

	public function getNaam(): ?string
	{
		return $this->naam;
	}

	public function setNaam(string $naam): self
	{
		$this->naam = $naam;

		return $this;
	}

	public function getOpmerkingen(): ?string
	{
		return $this->opmerkingen;
	}

	public function setOpmerkingen(?string $opmerkingen): self
	{
		$this->opmerkingen = $opmerkingen;

		return $this;
	}

	public function getTotaal(): float
	{
		return $this->totaal;
	}

	public function setTotaal(float $totaal): self
	{
		$this->totaal = $totaal;

		return $this;
	}

	public function getBeoordelaar(): ?Profiel
	{
		return $this->beoordelaar;
	}

	public function setBeoordelaar(?Profiel $beoordelaar): self
	{
		$this->beoordelaar = $beoordelaar;

		return $this;
	}

	public function getNummer(): ?string
	{
		return $this->nummer;
	}

	public function setNummer(string $nummer): self
	{
		$this->nummer = $nummer;

		return $this;
	}

	public function isIngediend(): bool
	{
		return $this->ingediend;
	}

	public function setIngediend(bool $ingediend): self
	{
		$this->ingediend = $ingediend;

		return $this;
	}

	public function getBeoordeeld(): bool
	{
		return $this->beoordeeld;
	}

	public function setBeoordeeld(bool $beoordeeld): self
	{
		$this->beoordeeld = $beoordeeld;

		return $this;
	}

	public function getGoedgekeurd(): bool
	{
		return $this->goedgekeurd;
	}

	public function setGoedgekeurd(bool $goedgekeurd): self
	{
		$this->goedgekeurd = $goedgekeurd;

		return $this;
	}

	/**
	 * @return Collection|DeclaratieBon[]
	 */
	public function getBonnen(): Collection
	{
		return $this->bonnen;
	}

	public function addBon(DeclaratieBon $bon): self
	{
		if (!$this->bonnen->contains($bon)) {
			$this->bonnen[] = $bon;
			$bon->setDeclaratie($this);
		}

		return $this;
	}

	public function removeBon(DeclaratieBon $bon): self
	{
		if ($this->bonnen->contains($bon)) {
			$this->bonnen->removeElement($bon);
			// set the owning side to null (unless already changed)
			if ($bon->getDeclaratie() === $this) {
				$bon->setDeclaratie(null);
			}
		}

		return $this;
	}

	public function fromParameters(ParameterBag $data): self
	{
		if ($data->get('omschrijving')) {
			$this->setOmschrijving($data->get('omschrijving'));
		} else {
			$this->setOmschrijving(null);
		}

		if ($data->get('betaalwijze') === 'C.S.R.-pas') {
			$this->setCsrPas(true);
			$this->setNaam($data->get('tnv'));
		} elseif ($data->get('betaalwijze') === 'voorgeschoten') {
			$this->setCsrPas(false);
			if ($data->getBoolean('eigenRekening') === true) {
				$this->setRekening($this->getIndiener()->bankrekening);
				$this->setNaam($this->getIndiener()->getNaam('voorletters'));
			} else {
				$this->setRekening($data->get('rekening'));
				$this->setNaam($data->get('tnv'));
			}
		}

		$this->setOpmerkingen($data->get('opmerkingen', ''));

		return $this;
	}

	public function getBedragExcl(): float
	{
		$som = 0;
		foreach ($this->getBonnen() as $bon) {
			$som += $bon->getBedragExcl();
		}
		return round($som, 2);
	}

	public function getBtwBedrag(): float
	{
		$som = 0;
		foreach ($this->getBonnen() as $bon) {
			$som += $bon->getBtwBedrag();
		}
		return round($som, 2);
	}

	public function getBedragIncl(): float
	{
		$som = 0;
		foreach ($this->getBonnen() as $bon) {
			$som += $bon->getBedragIncl();
		}
		return round($som, 2);
	}
}
