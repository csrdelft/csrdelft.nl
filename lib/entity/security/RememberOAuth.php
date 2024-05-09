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
#[ORM\Table('oauth2_remember')]
#[ORM\UniqueConstraint(name: 'account_client', columns: ['uid', 'client_identifier'])]
#[ORM\Entity(repositoryClass: RememberOAuthRepository::class)]
class RememberOAuth implements DataTableEntry
{
	/**
  * Primary key
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[Serializer\Groups('datatable')]
 public $id;
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[Serializer\Groups('datatable')]
 public $uid;
	/**
  * Identifier in oauth2_client
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('datatable')]
 public $clientIdentifier;
	/**
  * @var Account
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Account::class)]
 public $account;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 #[Serializer\Groups('datatable')]
 public $rememberSince;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 #[Serializer\Groups('datatable')]
 public $lastUsed;
	/**
  * OAuth2 scopes voor deze sessie.
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $scopes;
}
