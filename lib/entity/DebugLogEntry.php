<?php

namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DebugLogEntry.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\DebugLogRepository::class)]
#[ORM\Table('debug_log')]
class DebugLogEntry
{
	/**
	 * Primary key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * Module controller and action with params
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $class_function;
	/**
	 * Dump data
	 * @var string LongText
	 */
	#[ORM\Column(type: 'longtext')]
	public $dump;
	/**
	 * Call trace
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $call_trace;
	/**
	 * DateTime
	 * @var string
	 */
	#[ORM\Column(type: 'datetime')]
	public $moment;
	/**
	 * Lidnummer
	 * @var string
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $uid;
	/**
	 * Lidnummer of original user
	 * @var string
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $su_uid;
	/**
	 * IP address
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $ip;
	/**
	 * Request URI
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $request;
	/**
	 * Referer
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $referer;
	/**
	 * User agent
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $user_agent;
}
