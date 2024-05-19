<?php

namespace CsrDelft\entity\fotoalbum;

use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Afbeelding;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Table('fotos')]
#[ORM\Entity(repositoryClass: FotoRepository::class)]
#[ORM\EntityListeners(['CsrDelft\events\FotoListener'])]
class Foto extends Afbeelding
{
	const FOTOALBUM_ROOT = '/fotoalbum';
	const THUMBS_DIR = '_thumbs';
	const RESIZED_DIR = '_resized';

	/**
  * Relatief pad in fotoalbum
  * @var string
  */
 #[ORM\Column(type: 'stringkey')]
 #[ORM\Id]
 public $subdir;
	/**
  * @var string
  */
 #[ORM\Column(type: 'stringkey')]
 #[ORM\Id]
 public $filename;
	/**
  * Degrees of rotation
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 public $rotation;
	/**
  * Uploader
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $owner;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'owner', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $owner_profiel;

	public function __construct(
		$filename = null,
		FotoAlbum $album = null,
		$parse = false
	) {
		if ($album !== null) {
			$this->filename = $filename;
			$this->directory = $album->path;
			$this->subdir = $album->subdir;

			if (
				!PathUtil::path_valid(
					PHOTOALBUM_PATH,
					PathUtil::join_paths($album->subdir, $filename)
				)
			) {
				throw new NotFoundHttpException(); // Voorkom traversal door filename
			}
		}
		parent::__construct(null, $parse);
	}

	public function getUUID(): string
	{
		return PathUtil::join_paths($this->subdir, $this->filename) .
			'@' .
			get_class($this) .
			'.csrdelft.nl';
	}

	public function getThumbPath(): string|array|null
	{
		return PathUtil::join_paths(
			PHOTOALBUM_PATH,
			$this->subdir,
			self::THUMBS_DIR,
			$this->filename
		);
	}

	public function getResizedPath(): string|array|null
	{
		return PathUtil::join_paths(
			PHOTOALBUM_PATH,
			$this->subdir,
			self::RESIZED_DIR,
			$this->filename
		);
	}

	public function getAlbumUrl(): string|array
	{
		return PathUtil::direncode(
			PathUtil::join_paths(self::FOTOALBUM_ROOT, $this->subdir)
		);
	}
	public function getAlbum(): FotoAlbum
	{
		return new FotoAlbum($this->subdir);
	}
	public function getFullUrl(): string|array
	{
		return PathUtil::direncode(
			PathUtil::join_paths(self::FOTOALBUM_ROOT, $this->subdir, $this->filename)
		);
	}

	public function getThumbUrl(): string|array
	{
		return PathUtil::direncode(
			PathUtil::join_paths(
				self::FOTOALBUM_ROOT,
				$this->subdir,
				self::THUMBS_DIR,
				$this->filename
			)
		);
	}

	public function getResizedUrl(): string|array
	{
		return PathUtil::direncode(
			PathUtil::join_paths(
				self::FOTOALBUM_ROOT,
				$this->subdir,
				self::RESIZED_DIR,
				$this->filename
			)
		);
	}

	public function hasThumb(): bool
	{
		$path = $this->getThumbPath();
		return file_exists($path) && is_file($path);
	}

	public function hasResized(): bool
	{
		$path = $this->getResizedPath();
		return file_exists($path) && is_file($path);
	}

	public function createThumb()
	{
		$path = PathUtil::join_paths(
			PHOTOALBUM_PATH,
			$this->subdir,
			self::THUMBS_DIR
		);
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command =
			$_ENV['IMAGEMAGICK'] .
			' ' .
			escapeshellarg($this->getFullPath()) .
			' -thumbnail 200x200^ -gravity center -extent 150x150 -format jpg -quality 80 -auto-orient ' .
			$rotate .
			escapeshellarg($this->getThumbPath());
		shell_exec($command);
		if ($this->hasThumb()) {
			chmod($this->getThumbPath(), 0644);
		} else {
			throw new CsrException('Thumb maken mislukt: ' . $this->getFullPath());
		}
	}

	public function createResized()
	{
		$path = PathUtil::join_paths(
			PHOTOALBUM_PATH,
			$this->subdir,
			self::RESIZED_DIR
		);
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command =
			$_ENV['IMAGEMAGICK'] .
			' ' .
			escapeshellarg($this->getFullPath()) .
			' -resize 1024x1024 -format jpg -quality 85 -interlace Line  -auto-orient ' .
			$rotate .
			escapeshellarg($this->getResizedPath());
		shell_exec($command);
		if ($this->hasResized()) {
			chmod($this->getResizedPath(), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getFullPath());
		}
	}

	public function isComplete(): bool
	{
		return $this->hasThumb() && $this->hasResized();
	}
}
