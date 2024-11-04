<?php

namespace CsrDelft\entity;

use CsrDelft\common\CsrException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Afbeelding;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ForumPlaatje
 * @package CsrDelft\entity
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\ForumPlaatjeRepository::class
	)
]
#[ORM\Table('forumplaatjes')]
#[ORM\Index(name: 'access_key', columns: ['access_key'])]
class ForumPlaatje
{
	/**
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'stringkey')]
	public $access_key;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $datum_toegevoegd;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'uid', nullable: true)]
	public $maker;
	/**
	 * @var Profiel|null
	 */
	#[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
	#[ORM\JoinColumn(name: 'maker', referencedColumnName: 'uid', nullable: true)]
	public $maker_profiel;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $source_url;

	public function exists()
	{
		return $this->getAfbeelding()->exists();
	}

	public function getAfbeelding(bool $resize = false): Afbeelding
	{
		return new Afbeelding($this->getPath($resize));
	}

	public function getPath(bool $resize = false): string
	{
		return PLAATJES_PATH . ($resize ? 'resized/' : '') . strval($this->id);
	}

	public function getUrl(bool $resized = false): string
	{
		return "/forum/plaatjes/bekijken/$this->access_key" .
			($resized ? '/resized' : '');
	}

	public function hasResized(): bool
	{
		$path = $this->getPath(true);
		return file_exists($path) && is_file($path);
	}
}
