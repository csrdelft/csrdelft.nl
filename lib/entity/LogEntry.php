<?php

namespace CsrDelft\entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * LogEntry.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\LogRepository")
 * @ORM\Table("log", indexes={
 *   @ORM\Index(name="uid", columns={"uid"}),
 *   @ORM\Index(name="moment", columns={"moment"})
 * })
 */
class LogEntry {

	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer", name="ID")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $ID;
	/**
	 * UID of user or x999
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $uid;
	/**
	 * IP address of user
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $ip;
	/**
	 * Position of user (if enabled)
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $locatie;
	/**
	 * DateTime
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $moment;
	/**
	 * Request URL
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $url;
	/**
	 * HTTP Referer
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $referer;
	/**
	 * User agent
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $useragent;

	public function getFormattedReferer() {
		if ($this->referer == '') {
			return '-';
		} else {
			if (preg_match('/google/i', $this->referer)) {
				$iQpos = 2 + strpos($this->referer, 'q=');
				$iLengte = strpos($this->referer, '&') - $iQpos - 3;
				return urldecode(substr($this->referer, $iQpos, $iLengte));
			} else {
				return $this->referer;
			}
		}
	}

	public function removeOverflow()
	{
		$this->url = substr($this->url, 0, min(strlen($this->url), 255));
		$this->referer = substr($this->referer, 0, min(strlen($this->referer), 255));
		$this->useragent = substr($this->useragent, 0, min(strlen($this->useragent), 255));
	}
}
