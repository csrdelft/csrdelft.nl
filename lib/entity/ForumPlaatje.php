<?php


namespace CsrDelft\entity;


use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Afbeelding;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ForumPlaatje
 * @package CsrDelft\entity
 * @ORM\Entity(repositoryClass="CsrDelft\repository\ForumPlaatjeRepository")
 * @ORM\Table("forumplaatjes")
 */
class ForumPlaatje {
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="stringkey")
	 */
	public $access_key;
	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $datum_toegevoegd;
	/**
	 * @var string
	 * @ORM\Column(type="uid", nullable=true)
	 */
	public $maker;
	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $source_url;

	public function exists() {
		return $this->getAfbeelding()->exists();
	}

	public function getAfbeelding($resize = false) {
		return new Afbeelding($this->getPath($resize));
	}

	public function getPath($resize = false) {
		return PLAATJES_PATH . ($resize ? "resized/" : "") . strval($this->id);
	}

	public function getUrl($resized = false) {
		return "/forum/plaatjes/bekijken/$this->access_key" . ($resized ? "/resized" : "");
	}

	public function createResized() {
		// Resize the smallest side of the image to at most 1024px
		$command = env('IMAGEMAGICK') . ' ' . escapeshellarg($this->getPath(false)) . ' -resize "750x>" -format jpg -quality 85 -interlace Line  -auto-orient ' . escapeshellarg($this->getPath(true));
		shell_exec($command);
		if ($this->hasResized()) {
			chmod($this->getPath(true), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getPath(true));
		}
	}

	public function hasResized() {
		$path = $this->getPath(true);
		return file_exists($path) && is_file($path);
	}
}
