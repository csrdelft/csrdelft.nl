<?php

namespace CsrDelft\entity\security;

use CsrDelft\Component\DataTable\DataTableEntry;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Een Account kan een oauth2_client trusten. Het is dan niet meer nodig om opnieuw the accepteren
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\security\RememberOAuthRepository::class
	)
]
#[ORM\Table('oauth2_remember')]
#[
	ORM\UniqueConstraint(
		name: 'account_client',
		columns: ['uid', 'client_identifier']
	)
]
class RememberOAuth implements DataTableEntry
{
	/**
	 * Primary key
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'uid')]
	public $uid;
	/**
	 * Identifier in oauth2_client
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $clientIdentifier;
	/**
	 * @var Account
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\security\Account::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $account;
	/**
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'datetime_immutable')]
	public $rememberSince;
	/**
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'datetime_immutable')]
	public $lastUsed;
	/**
	 * OAuth2 scopes voor deze sessie.
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $scopes;
}
