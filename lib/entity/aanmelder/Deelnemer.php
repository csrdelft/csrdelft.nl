<?php

namespace CsrDelft\entity\aanmelder;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\aanmelder\DeelnemerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeelnemerRepository::class)
 * @ORM\Table(name="aanmelder_deelnemer")
 */
class Deelnemer
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=AanmeldActiviteit::class, inversedBy="deelnemers")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $activiteit;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $lid;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $aantal;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $aangemeld;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $aanwezig = null;

	public function __construct(
		AanmeldActiviteit $activiteit,
		Profiel $lid,
		int $aantal
	) {
		$this->activiteit = $activiteit;
		$this->lid = $lid;
		$this->aantal = $aantal;
		$this->aangemeld = date_create_immutable();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getActiviteit(): ?AanmeldActiviteit
	{
		return $this->activiteit;
	}

	public function setActiviteit(?AanmeldActiviteit $activiteit): self
	{
		$this->activiteit = $activiteit;

		return $this;
	}

	public function getAantal(): int
	{
		return $this->aantal;
	}

	public function setAantal(int $aantal): self
	{
		$this->aantal = $aantal;

		return $this;
	}

	public function getAangemeld(): DateTimeImmutable
	{
		return $this->aangemeld;
	}

	public function setAangemeld(DateTimeImmutable $aangemeld): self
	{
		$this->aangemeld = $aangemeld;

		return $this;
	}

	public function setLid(Profiel $lid): Deelnemer
	{
		$this->lid = $lid;

		return $this;
	}

	public function getLid(): Profiel
	{
		return $this->lid;
	}

	public function isAanwezig(): bool
	{
		return $this->aanwezig !== null;
	}

	public function getAanwezigTijd(): string
	{
		return $this->isAanwezig()
			? DateUtil::dateFormatIntl($this->aanwezig, 'H:mm')
			: '';
	}

	public function setAanwezig()
	{
		$this->aanwezig = date_create_immutable();
	}

	public function setNietAanwezig()
	{
		$this->aanwezig = null;
	}
}
