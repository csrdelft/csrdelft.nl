<?php

namespace CsrDelft\entity\fotoalbum;

use CsrDelft\repository\ProfielRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\fotoalbum\FotoTagsRepository")
 * @ORM\Table("foto_tags")
 */
class FotoTag implements JsonSerializable
{
	/**
	 * Unique Universal Identifier
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $refuuid;
	/**
	 * Single keyword
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $keyword;
	/**
	 * Getagged door
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $door;
	/**
	 * Gemaakt op datum en tijd
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $wanneer;
	/**
	 * X-coord
	 * @var float
	 * @ORM\Column(type="float")
	 */
	public $x;
	/**
	 * Y-coord
	 * @var float
	 * @ORM\Column(type="float")
	 */
	public $y;
	/**
	 * Size
	 * @var float
	 * @ORM\Column(type="float")
	 */
	public $size;

	public function jsonSerialize()
	{
		$array = (array)$this;
		$array['name'] = ProfielRepository::getNaam($this->keyword, 'user');
		return $array;
	}
}
