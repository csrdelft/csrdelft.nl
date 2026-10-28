<?php

namespace CsrDelft\entity\panorama;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(name: 'IDX_PANORAMA_HELP_TEAM', columns: ['team_id'])]
#[ORM\Index(name: 'IDX_PANORAMA_HELP_HELPER', columns: ['helper'])]
#[
	ORM\Table(
		name: 'panorama_help',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
class ExpeditieHulp
{
	#[ORM\Id, ORM\ManyToOne(targetEntity: ExpeditieSpeler::class)]
	#[
		ORM\JoinColumn(
			name: 'recipient',
			referencedColumnName: 'uid',
			nullable: false,
			onDelete: 'CASCADE'
		)
	]
	public ExpeditieSpeler $recipient;
	#[ORM\Id, ORM\Column(type: 'smallint', options: ['unsigned' => true])]
	public int $stage;
	#[ORM\Id, ORM\ManyToOne(targetEntity: ExpeditieSpeler::class)]
	#[
		ORM\JoinColumn(
			name: 'helper',
			referencedColumnName: 'uid',
			nullable: false,
			onDelete: 'CASCADE'
		)
	]
	public ExpeditieSpeler $helper;
	#[ORM\ManyToOne(targetEntity: ExpeditieTeam::class)]
	#[
		ORM\JoinColumn(
			name: 'team_id',
			referencedColumnName: 'id',
			nullable: false,
			onDelete: 'CASCADE'
		)
	]
	public ExpeditieTeam $team;
	#[ORM\Column(type: 'datetime')]
	public \DateTimeImmutable $created_at;
}
