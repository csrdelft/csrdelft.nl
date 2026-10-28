<?php
namespace CsrDelft\entity\panorama;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[
	ORM\Table(
		name: 'panorama_game_settings',
		options: ['charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci']
	)
]
class PanoramaGameConfiguration
{
	#[ORM\Id, ORM\Column(type: 'integer')]
	public int $id;
	#[ORM\Column(type: 'text', length: 16777215)]
	public string $settings;
	#[ORM\Column(type: 'string', length: 32)]
	public string $updated_by;
}
