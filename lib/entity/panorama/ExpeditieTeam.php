<?php

namespace CsrDelft\entity\panorama;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_PANORAMA_CODE', columns: ['code'])]
#[
	ORM\Table(
		name: 'panorama_teams',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
class ExpeditieTeam
{
	#[
		ORM\Id,
		ORM\GeneratedValue,
		ORM\Column(type: 'integer', options: ['unsigned' => true])
	]
	public int $id;
	#[ORM\Column(type: 'string', length: 60)]
	public string $name;
	#[ORM\Column(type: 'string', length: 8, options: ['fixed' => true])]
	public string $code;
	#[ORM\Column(type: 'datetime')]
	public \DateTimeImmutable $created_at;
}
