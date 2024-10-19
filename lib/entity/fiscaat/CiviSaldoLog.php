<?php

namespace CsrDelft\entity\fiscaat;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package CsrDelft\entity\fiscaat
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\fiscaat\CiviSaldoLogRepository::class
	)
]
class CiviSaldoLog
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $ip; // TODO Dit is een CiviSaldoLogEnum
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $type;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $data;
	/**
	 * @var \DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
	public $timestamp;
}
