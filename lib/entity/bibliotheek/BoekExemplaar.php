<?php

namespace CsrDelft\entity\bibliotheek;

use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use Boek;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\model\entity\bibliotheek
 */
#[ORM\Table('biebexemplaar')]
#[ORM\Entity(repositoryClass: BoekExemplaarRepository::class)]
class BoekExemplaar
{
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer', options: ['default' => 0])]
 public $boek_id;
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $eigenaar_uid;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'eigenaar_uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $eigenaar;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $opmerking;

	/**
  * @var string
  */
 #[ORM\Column(type: 'uid', nullable: true)]
 public $uitgeleend_uid;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uitgeleend_uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $uitgeleend;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime')]
 public $toegevoegd;
	/**
  * @var BoekExemplaarStatus
  */
 #[ORM\Column(type: 'enumBoekExemplaarStatus')]
 public $status;
	/**
  * @var DateTimeImmutable|null
  */
 #[ORM\Column(type: 'datetime', nullable: true)]
 public $uitleendatum;
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer', options: ['default' => 0])]
 public $leningen;

	/**
  * @var Boek
  */
 #[ORM\ManyToOne(targetEntity: Boek::class, inversedBy: 'exemplaren')]
 public $boek;

	public function isBiebBoek(): bool
	{
		return $this->eigenaar_uid == 'x222';
	}

	public function isEigenaar(): bool
	{
		if ($this->eigenaar_uid == LoginService::getUid()) {
			return true;
		} elseif ($this->isBiebBoek() && LoginService::mag(P_BIEB_MOD)) {
			return true;
		}
		return false;
	}

	public function magBewerken(): bool
	{
		return $this->isEigenaar();
	}

	public function magBekijken(): bool
	{
		return LoginService::mag(P_BIEB_READ) || $this->magBewerken();
	}

	public function isBeschikbaar(): bool
	{
		return $this->status === BoekExemplaarStatus::beschikbaar();
	}

	public function kanLenen(string $uid): bool
	{
		return $this->eigenaar_uid != $uid && $this->isBeschikbaar();
	}

	public function isUitgeleend(): bool
	{
		return $this->status === BoekExemplaarStatus::uitgeleend();
	}

	public function isTeruggegeven(): bool
	{
		return $this->status === BoekExemplaarStatus::teruggegeven();
	}

	public function isVermist(): bool
	{
		return $this->status === BoekExemplaarStatus::vermist();
	}
}
