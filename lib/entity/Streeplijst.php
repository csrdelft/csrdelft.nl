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
#[ORM\Entity(repositoryClass: StreeplijstRepository::class)]
#[ORM\Table('streeplijsten')]
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
	#[ORM\Column(type: 'datetime')]
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

	public function getLeden()
	{
		return explode(';', $this->leden_streeplijst);
	}

	public function getInhoud()
	{
		return explode(';', $this->inhoud_streeplijst);
	}
}
