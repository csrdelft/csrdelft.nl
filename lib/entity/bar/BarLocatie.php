<?php

namespace CsrDelft\entity\bar;

use CsrDelft\entity\security\Account;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class BarLocatie
 * @package CsrDelft\entity\bar
 */
#[ORM\Entity]
class BarLocatie
{
	/**
	 * @var integer
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\GeneratedValue]
	#[ORM\Id]
	public $id;

	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: Account::class)]
	#[ORM\JoinColumn(referencedColumnName: 'uid')]
	public $doorAccount;

	/**
	 * @var string
	 */
	#[Serializer\Groups('json')]
	#[ORM\Column(type: 'string')]
	public $ip;

	/**
	 * @var string
	 */
	#[Serializer\Groups('json')]
	#[ORM\Column(type: 'string')]
	public $naam;

	/**
	 * @var string
	 */
	#[Serializer\Groups('json')]
	#[ORM\Column(type: 'uuid')]
	public $sleutel;
}
