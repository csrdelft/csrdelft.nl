<?php

namespace CsrDelft\entity;

use CsrDelft\repository\StreeplijstRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Class Streeplijst.
 *
 * @author J. de Jong
 */
#[ORM\Table('streeplijsten')]
#[ORM\Entity(repositoryClass: StreeplijstRepository::class)]
class Streeplijst
{
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\GeneratedValue]
 #[ORM\Id]
 public $id;

	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $maker;

	/**
  * @var DateTime
  */
 #[ORM\Column(type: 'datetime_immutable')]
 public $aanmaakdatum;

	/**
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 public $inhoud_streeplijst;

	/**
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 public $leden_streeplijst;

	/**
  * @var string
  */
 #[ORM\Column(type: 'string', nullable: true)]
 public $naam_streeplijst;

	public function getLeden(): array
	{
		return explode(';', $this->leden_streeplijst);
	}

	public function getInhoud(): array
	{
		return explode(';', $this->inhoud_streeplijst);
	}
}
