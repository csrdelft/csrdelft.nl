<?php

namespace CsrDelft\entity\security;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Account
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Login account.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\AccountRepository")
 * @ORM\Table("accounts")
 */
class Account implements UserInterface, PasswordAuthenticatedUserInterface
{
	/**
	 * Lidnummer
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;

	/**
	 * Unieke id voor externe applicaties
	 * @var Uuid
	 * @ORM\Column(type="uuid", unique=true)
	 */
	public $uuid;
	/**
	 * Gebruikersnaam
	 * @var string
	 * @ORM\Column(type="stringkey", unique=true)
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
	 * @ORM\Column(type="datetime", nullable=true)
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

	public function hasPrivateToken()
	{
		return !empty($this->private_token);
	}

	public function getICalLink()
	{
		$url = '/agenda/ical/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.ics';
		} else {
			return $url . $this->private_token . '/csrdelft.ics';
		}
	}

	public function getRssLink()
	{
		$url = '/forum/rss/';
		if (empty($this->private_token)) {
			return $url . 'csrdelft.xml';
		} else {
			return $url . $this->private_token . '/csrdelft.xml';
		}
	}

	public function getEmail()
	{
		return $this->email;
	}

	//****
	// UserInterface implementatie
	//****

	public function getRoles(): array
	{
		return [str_replace('R_', 'ROLE_', $this->perm_role)];
	}

	public function getPassword(): string
	{
		return $this->pass_hash;
	}

	public function getSalt()
	{
		return '';
	}

	public function getUsername()
	{
		return $this->uid;
	}

	public function getUserIdentifier(): string
	{
		return $this->uid;
	}

	public $pass_plain;

	public function eraseCredentials()
	{
		$this->pass_plain = null;
	}
}
