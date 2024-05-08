<?php

namespace CsrDelft\entity\fotoalbum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\HostUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\Map;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FotoAlbum.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Table('fotoalbums')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\fotoalbum\FotoAlbumRepository::class)]
#[ORM\EntityListeners(['CsrDelft\events\FotoAlbumListener'])]
class FotoAlbum extends Map
{
	/**
  * Relatief pad in fotoalbum
  * @var string
  */
 #[ORM\Column(type: 'stringkey')]
 #[ORM\Id]
 public $subdir;
	/**
	 * Subalbums in dit album
	 * @var FotoAlbum[]
	 */
	protected $subalbums;
	/**
	 * Fotos in dit album
	 * @var Foto[]
	 */
	protected $fotos;
	/**
	 * Fotos zonder thumb of resized
	 * @var Foto[]
	 */
	protected $fotos_incompleet;
	/**
  * Creator
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $owner;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'owner', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\profiel\Profiel::class)]
 public $owner_profiel;

	public function __construct($path = null, $absolute = false)
	{
		if ($path === null) {
			$this->path = PathUtil::realpathunix(
				PathUtil::join_paths(PHOTOALBUM_PATH, $this->subdir)
			);
		} elseif (
			$absolute == true &&
			str_starts_with(
				PathUtil::realpathunix($path),
				PathUtil::realpathunix(PHOTOALBUM_PATH)
			)
		) {
			// Check that $path is inside PHOTOALBUM_PATH
			$this->path = rtrim($path, '/');
			$this->subdir = substr(
				$this->path,
				strlen(PathUtil::realpathunix(PHOTOALBUM_PATH) . '/')
			);
		} elseif (PathUtil::path_valid(PHOTOALBUM_PATH, $path)) {
			// Check if $path not trying to traverse outside PHOTOALBUM_PATH
			$this->path = rtrim(
				PathUtil::realpathunix(PathUtil::join_paths(PHOTOALBUM_PATH, $path)),
				'/'
			);
			//We verwijderen het beginstuk van de string
			$this->subdir = $path;
		} else {
			throw new NotFoundHttpException('Fotoalbum niet gevonden');
		}
		$this->dirname = basename($this->path);
	}

	public function getPath(): string
	{
		return $this->path ?? PathUtil::join_paths(PHOTOALBUM_PATH, $this->subdir);
	}

	/**
	 * File modification time van het album.
	 */
	public function modified(): int|false
	{
		return filemtime($this->path);
	}

	public function getParentName(): string
	{
		return ucfirst(basename(dirname($this->subdir)));
	}

	public function getUrl(): string
	{
		return '/fotoalbum/' . PathUtil::direncode($this->subdir);
	}

	public function isEmpty(): bool
	{
		$subalbums = $this->getSubAlbums();
		return empty($subalbums) && !$this->hasFotos(true);
	}

	public function hasFotos($incompleet = false): bool
	{
		$fotos = $this->getFotos($incompleet);
		return !empty($fotos);
	}

	/**
	 * @param false $incompleet
	 * @return Foto[]
	 */
	public function getFotos($incompleet = false): array
	{
		if (!isset($this->fotos)) {
			$this->fotos = [];
			$this->fotos_incompleet = [];

			$scan = scandir($this->path, SCANDIR_SORT_ASCENDING);
			if (empty($scan)) {
				return [];
			}
			foreach ($scan as $entry) {
				if (is_file(PathUtil::join_paths($this->path, $entry))) {
					$foto = new Foto($entry, $this);
					if ($foto->isComplete()) {
						$this->fotos[] = $foto;
					} else {
						$this->fotos_incompleet[] = $foto;
					}
				}
			}
		}
		if ($incompleet) {
			return array_merge($this->fotos, $this->fotos_incompleet);
		} else {
			return $this->fotos;
		}
	}

	public function orderByDateModified(): void
	{
		$order = [];
		foreach ($this->getFotos() as $i => $foto) {
			$order[$i] = filemtime($foto->getFullPath());
		}
		arsort($order);
		$result = [];
		foreach ($order as $i => $mtime) {
			$result[] = $this->fotos[$i];
		}
		$this->fotos = $result;
	}

	public function getSubAlbums($recursive = false): bool|array
	{
		if (!isset($this->subalbums)) {
			$this->subalbums = [];

			$scan = scandir($this->path, SCANDIR_SORT_DESCENDING);
			if (empty($scan)) {
				return false;
			}
			foreach ($scan as $entry) {
				if (
					substr($entry, 0, 1) !== '.' &&
					substr($entry, 0, 1) !== '_' &&
					is_dir(PathUtil::join_paths($this->path, $entry))
				) {
					$subalbum = ContainerFacade::getContainer()
						->get(FotoAlbumRepository::class)
						->getFotoAlbum(PathUtil::join_paths($this->subdir, $entry));
					if ($subalbum) {
						$this->subalbums[] = $subalbum;
						if ($recursive) {
							$subalbum->getSubalbums(true);
						}
					}
				}
			}
		}
		return $this->subalbums;
	}

	/**
	 * @return string[]
	 */
	public function getCoverUrls(): array
	{
		$fotos = [];
		$fotos[] = $this->getCoverUrl();
		$fotos[] = $this->getRandomCover();
		$fotos[] = $this->getRandomCover();

		return $fotos;
	}

	public function getRandomCover(): string
	{
		if ($this->hasFotos()) {
			// Anders een willekeurige foto:
			$count = count($this->fotos);
			if ($count > 0) {
				$idx = rand(0, $count - 1);
				return $this->fotos[$idx]->getThumbUrl();
			}
		}
		// Foto uit willekeurig subalbum:
		$count = count($this->getSubAlbums());
		if ($count > 0) {
			$idx = rand(0, $count - 1);
			return $this->subalbums[$idx]->getCoverUrl();
		}
		// If all else fails:
		return '/plaetjes/_geen_thumb.jpg';
	}

	public function getCoverUrl(): string
	{
		if ($this->hasFotos() && $this->dirname !== 'Posters') {
			foreach ($this->getFotos() as $foto) {
				if (strpos($foto->filename, 'folder') !== false) {
					return $foto->getThumbUrl();
				}
			}
		}
		return $this->getRandomCover();
	}

	public function getMostRecentSubAlbum(): FotoAlbum
	{
		$recent = $this;
		foreach ($this->getSubAlbums() as $subalbum) {
			if ($subalbum->modified() > $recent->modified()) {
				$recent = $subalbum->getMostRecentSubAlbum();
			}
		}
		return $recent;
	}

	/**
	 * Zegt of dit album publiek toegankelijk is.
	 * @return bool
	 */
	public function isPubliek()
	{
		return preg_match('/Publiek\/?.*$/', $this->subdir) == 1;
	}

	/**
	 * Maak een object voor jGallery.
	 *
	 * @return string[][]
	 */
	public function getAlbumArrayRecursive(): array
	{
		$fotos = [];
		foreach ($this->getFotos() as $foto) {
			$fotos[] = [
				'url' => $foto->getResizedUrl(),
				'fullUrl' => HostUtil::getCsrRoot() . $foto->getFullUrl(),
				'thumbUrl' => $foto->getThumbUrl(),
				'title' => '',
				'hash' => str_replace(' ', '%20', urldecode($foto->getFullUrl())),
			];
		}

		$hoofdAlbum = [
			'title' => ucfirst($this->dirname),
			'items' => $fotos,
		];

		$albums = [$hoofdAlbum];

		foreach ($this->getSubAlbums() as $subAlbum) {
			if ($subAlbum->hasFotos()) {
				$albums = array_merge($albums, $subAlbum->getAlbumArrayRecursive());
			}
		}

		return $albums;
	}

	/**
	 * Album array zonder poespas. Wordt voor sliders gebruikt.
	 *
	 * @return string[][]
	 */
	public function getAlbumArray(): array
	{
		$fotos = [];
		foreach ($this->getFotos() as $foto) {
			$fotos[] = [
				'url' => $foto->getResizedUrl(),
				'fullUrl' => HostUtil::getCsrRoot() . $foto->getFullUrl(),
				'thumbUrl' => $foto->getThumbUrl(),
				'title' => '',
				'hash' => str_replace(' ', '%20', urldecode($foto->getFullUrl())),
			];
		}

		return $fotos;
	}
}
