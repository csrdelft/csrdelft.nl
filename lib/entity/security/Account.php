<?php

namespace CsrDelft\entity\security;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Login account.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\AccountRepository")
 * @ORM\Table("accounts", indexes={
 *   @ORM\Index(name="username", columns={"username"})
 * })
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Account {

	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * Gebruikersnaam
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $username;
	/**
	 * E-mail address
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $email;
	/**
	 * Password hash
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $pass_hash;
	/**
	 * DateTime last change
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $pass_since;
	/**
	 * DateTime last successful login
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $last_login_success;
	/**
	 * DateTime last login attempt
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $last_login_attempt;
	/**
	 * Amount of failed login attempts
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $failed_login_attempts;
	/**
	 * Reden van blokkering
	 * @var string|null
	 * @ORM\Column(type="text", nullable=true)
	 */
	public $blocked_reason;
	/**
	 * RBAC permissions role
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $perm_role;
	/**
	 * RSS & ICAL token
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $private_token;
	/**
	 * DateTime last change
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $private_token_since;

	/**
	 * @var Profiel
	 * @ORM\OneToOne(targetEntity="CsrDelft\entity\profiel\Profiel", inversedBy="account")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	public function hasPrivateToken() {
		return !empty($this->private_token);
	}

	public function getICalLink() {
		$url = CSR_ROOT . '/agenda/ical/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.ics';
		} else {
			return $url . $this->private_token . '/csrdelft.ics';
		}
	}

	public function getRssLink() {
		$url = CSR_ROOT . '/forum/rss/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.xml';
		} else {
			return $url . $this->private_token . '/csrdelft.xml';
		}
	}

}
