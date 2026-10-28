<?php
namespace CsrDelft\entity\panorama;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[
	ORM\Table(
		name: 'panorama_daily_questions',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
#[ORM\UniqueConstraint(name: 'UNIQ_PANORAMA_DAY', columns: ['day'])]
class PanoramaDailyQuestion
{
	#[
		ORM\Id,
		ORM\GeneratedValue,
		ORM\Column(type: 'integer', options: ['unsigned' => true])
	]
	public int $id;
	#[ORM\Column(type: 'date')]
	public \DateTimeImmutable $day;
	#[ORM\Column(type: 'string', length: 180)]
	public string $prompt;
	#[ORM\Column(type: 'text', length: 65535)]
	public string $answers;
	#[ORM\Column(type: 'string', length: 80, nullable: true)]
	public ?string $image = null;
	#[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
	public string $credit = '';
	#[ORM\Column(type: 'boolean', options: ['default' => false])]
	public bool $published = false;
	#[ORM\Column(type: 'string', length: 32)]
	public string $created_by;
	#[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
	public int $revision = 0;
	#[ORM\Column(type: 'datetime')]
	public \DateTimeImmutable $updated_at;
}
