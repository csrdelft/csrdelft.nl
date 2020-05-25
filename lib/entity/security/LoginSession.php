<?php

namespace CsrDelft\entity\security;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Table("login_sessions")
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\LoginSessionRepository")
 */
class LoginSession implements DataTableEntry {
	/**
	 * Primary key
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 * @Serializer\Groups("datatable")
	 */
	public $session_hash;
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @Serializer\Groups("datatable")
	 */
	public $uid;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
	/**
	 * DateTime
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $login_moment;
	/**
	 * DateTime
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $expire;
	/**
	 * User agent
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $user_agent;
	/**
	 * IP address
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("datatable")
	 */
	public $lock_ip;
	/**
	 * AuthenticationMethod
	 * @var string
	 * @ORM\Column(type="string")
	 * TODO is eigenlijk Authenticationmethod
	 */
	public $authentication_method;

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("login_moment")
	 */
	public function getDataTableLoginMoment() {
		return reldate($this->login_moment);
	}

	/**
	 * @return string
	 * @throws Exception
	 * @Serializer\SerializedName("authentication_method")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableAuthenticationMethod() {
		return AuthenticationMethod::from($this->authentication_method)->getDescription();
	}

	public function isRecent() {
		$recent = (int)instelling('beveiliging', 'recent_login_seconds');
		if (time() - $this->login_moment->getTimestamp() < $recent) {
			return true;
		}
		return false;
	}

}
