<?php

namespace CsrDelft\entity;

use CsrDelft\repository\ForumPlaatjeRepository;
use CsrDelft\common\CsrException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Afbeelding;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ForumPlaatje
 * @package CsrDelft\entity
 */
#[ORM\Table('forumplaatjes')]
#[ORM\Index(name: 'access_key', columns: ['access_key'])]
#[ORM\Entity(repositoryClass: ForumPlaatjeRepository::class)]
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
 #[ORM\Column(type: 'datetime_immutable')]
 public $datum_toegevoegd;
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid', nullable: true)]
 public $maker;
	/**
  * @var Profiel|null
  */
 #[ORM\JoinColumn(name: 'maker', referencedColumnName: 'uid', nullable: true)]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $maker_profiel;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 public $source_url;

	public function exists(): bool
	{
		return $this->getAfbeelding()->exists();
	}

	public function getAfbeelding($resize = false): Afbeelding
	{
		return new Afbeelding($this->getPath($resize));
	}

	public function getPath($resize = false): string
	{
		return PLAATJES_PATH . ($resize ? 'resized/' : '') . strval($this->id);
	}

	public function getUrl($resized = false): string
	{
		return "/forum/plaatjes/bekijken/$this->access_key" .
			($resized ? '/resized' : '');
	}

	public function createResized()
	{
		// Resize the smallest side of the image to at most 1024px
		$command =
			$_ENV['IMAGEMAGICK'] .
			' ' .
			escapeshellarg($this->getPath(false)) .
			' -resize "750x>" -format jpg -quality 85 -interlace Line  -auto-orient ' .
			escapeshellarg($this->getPath(true));
		shell_exec($command);
		if ($this->hasResized()) {
			chmod($this->getPath(true), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getPath(true));
		}
	}

	public function hasResized(): bool
	{
		$path = $this->getPath(true);
		return file_exists($path) && is_file($path);
	}
}
