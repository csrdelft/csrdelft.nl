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
 #[ORM\JoinColumn(referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Account::class)]
 public $doorAccount;

	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('json')]
 public $ip;

	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('json')]
 public $naam;

	/**
  * @var string
  */
 #[ORM\Column(type: 'uuid')]
 #[Serializer\Groups('json')]
 public $sleutel;
}
