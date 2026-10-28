<?php

namespace CsrDelft\entity\panorama;

use Doctrine\ORM\Mapping as ORM;

/** DBAL voert de speltransacties uit; deze mapping houdt Doctrine-schema en migratie gelijk. */
#[ORM\Entity]
#[ORM\Index(name: 'IDX_PANORAMA_TEAM', columns: ['team_id'])]
#[ORM\Index(name: 'IDX_PANORAMA_SCORE', columns: ['score', 'stage'])]
#[
	ORM\Table(
		name: 'panorama_players',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
class ExpeditieSpeler
{
	#[ORM\Id, ORM\Column(type: 'string', length: 32)]
	public string $uid;
	#[ORM\Column(type: 'string', length: 160)]
	public string $display_name;
	#[ORM\ManyToOne(targetEntity: ExpeditieTeam::class)]
	#[
		ORM\JoinColumn(
			name: 'team_id',
			referencedColumnName: 'id',
			nullable: true,
			onDelete: 'SET NULL'
		)
	]
	public ?ExpeditieTeam $team = null;
	#[ORM\Column(type: 'text', length: 4294967295)]
	public string $state;
	#[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
	public int $score = 0;
	#[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
	public int $stage = 0;
	#[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
	public int $revision = 0;
	#[ORM\Column(type: 'datetime')]
	public \DateTimeImmutable $updated_at;
}
