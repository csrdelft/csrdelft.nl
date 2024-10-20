<?php

namespace CsrDelft\entity\security;

use CsrDelft\repository\security\RememberOAuthRepository;
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
#[ORM\Entity(repositoryClass: RememberOAuthRepository::class)]
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
	#[ORM\ManyToOne(targetEntity: Account::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $account;
	/**
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'datetime')]
	public $rememberSince;
	/**
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'datetime')]
	public $lastUsed;
	/**
	 * OAuth2 scopes voor deze sessie.
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $scopes;
}
