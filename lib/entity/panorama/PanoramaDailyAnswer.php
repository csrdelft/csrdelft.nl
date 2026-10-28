<?php
namespace CsrDelft\entity\panorama;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[
	ORM\Table(
		name: 'panorama_daily_answers',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
#[ORM\Index(name: 'IDX_PANORAMA_DAILY_UID', columns: ['uid'])]
class PanoramaDailyAnswer
{
	#[ORM\Id, ORM\ManyToOne(targetEntity: PanoramaDailyQuestion::class)]
	#[
		ORM\JoinColumn(
			name: 'question_id',
			referencedColumnName: 'id',
			nullable: false,
			onDelete: 'CASCADE'
		)
	]
	public PanoramaDailyQuestion $question;
	#[ORM\Id, ORM\Column(type: 'string', length: 32)]
	public string $uid;
	#[ORM\Column(type: 'string', length: 160)]
	public string $display_name;
	#[ORM\Column(type: 'text', length: 65535)]
	public string $guesses;
	#[ORM\Column(type: 'boolean', options: ['default' => false])]
	public bool $solved = false;
	#[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
	public int $points = 0;
	#[ORM\Column(type: 'datetime', nullable: true)]
	public ?\DateTimeImmutable $answered_at = null;
	#[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
	public int $revision = 0;
}
