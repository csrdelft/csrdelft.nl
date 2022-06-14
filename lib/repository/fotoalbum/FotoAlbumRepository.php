<?php

namespace CsrDelft\repository\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\Entity\FotoAlbumVoter;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\entity\fotoalbum\FotoTagAlbum;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class FotoAlbumRepository extends AbstractRepository
{
	/**
	 * @var FotoRepository
	 */
	private $fotoRepository;
	/**
	 * @var FotoTagsRepository
	 */
	private $fotoTagsRepository;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		ManagerRegistry $registry,
		Security $security,
		FotoRepository $fotoRepository,
		FotoTagsRepository $fotoTagsRepository
	) {
		parent::__construct($registry, FotoAlbum::class);

		$this->fotoRepository = $fotoRepository;
		$this->fotoTagsRepository = $fotoTagsRepository;
		$this->security = $security;
	}

	/**
	 * @param string $dir
	 * @param int $limit
	 * @return FotoAlbum[]
	 */
	public function zoeken($dir, $limit)
	{
		return $this->createQueryBuilder('fa')
			->where('fa.subdir LIKE :subdir')
			->setParameter('subdir', '%' . $dir . '%')
			->orderBy('fa.subdir', 'DESC')
			->setMaxResults($limit)
			->getQuery()
			->getResult();
	}

	/**
	 * @param string $subdir
	 * @return FotoAlbum[]
	 */
	public function findBySubdir($subdir)
	{
		return $this->createQueryBuilder('fa')
			->where('fa.subdir LIKE :subdir')
			->setParameter('subdir', $subdir . '%')
			->getQuery()
			->getResult();
	}

	/**
	 * @param FotoAlbum $album
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(FotoAlbum $album)
	{
		if (!file_exists($album->getPath())) {
			mkdir($album->getPath());
			if (false === @chmod($album->getPath(), 0755)) {
				throw new CsrException(
					'Geen eigenaar van album: ' . htmlspecialchars($album->path)
				);
			}
		}
		$album->owner = $this->security->getUser()->getUsername();
		$album->owner_profiel = $this->security->getUser()->profiel;

		$this->getEntityManager()->persist($album);
		$this->getEntityManager()->flush();
	}

	/**
	 * @param FotoAlbum $album
	 * @return void
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(FotoAlbum $album)
	{
		$path = $album->path . '_resized';
		if (file_exists($path)) {
			rmdir($path);
		}
		$path = $album->path . '_thumbs';
		if (file_exists($path)) {
			rmdir($path);
		}
		if (file_exists($album->path)) {
			rmdir($album->path);
		}

		$this->getEntityManager()->remove($album);
		$this->getEntityManager()->flush();
	}

	public function getFotoAlbum($path)
	{
		if (
			AccountRepository::isValidUid($path) and
			ProfielRepository::existsUid($path)
		) {
			$album = new FotoTagAlbum($path);
		} else {
			$album = new FotoAlbum($path);
		}
		if (!$album->exists()) {
			throw new NotFoundHttpException("Fotoalbum $path bestaat niet");
		}
		if (!$this->security->isGranted(FotoAlbumVoter::BEKIJKEN, $album)) {
			throw new NotFoundHttpException();
		}
		return $album;
	}

	public function verwerkFotos(FotoAlbum $fotoalbum)
	{
		// verwijder niet bestaande subalbums en fotos uit de database
		$this->opschonen($fotoalbum);
		//define('RESIZE_OUTPUT', null);
		//echo '<h1>Fotoalbum verwerken: ' . $album->dirname . '</h1>Dit kan even duren...<br />';
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$fotoalbum->path,
				RecursiveDirectoryIterator::SKIP_DOTS |
					RecursiveDirectoryIterator::UNIX_PATHS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$albums = 0;
		$fotos = 0;
		$errors = 0;
		foreach ($iterator as $path => $object) {
			// skip _thumbs & _resized
			if (strpos($path, '/_') !== false) {
				continue;
			}
			try {
				// FotoAlbum
				if ($object->isDir()) {
					$albums++;
					$album = new FotoAlbum($path, true);
					if (!$this->find($album->subdir)) {
						$this->create($album);
					}
					if (false === @chmod($path, 0755)) {
						throw new CsrException('Geen eigenaar van album: ' . $path);
					}
				}
				// Foto
				else {
					$filename = basename($path);
					if ($filename === 'Thumbs.db') {
						unlink($path);
						continue;
					}
					$fotos++;
					$album = new FotoAlbum(dirname($path), true);
					$foto = new Foto($filename, $album, true);
					if (!$foto->exists()) {
						throw new CsrException(
							'Foto bestaat niet: ' . $foto->directory . $foto->filename
						);
					}
					$this->fotoRepository->verwerkFoto($foto);
					if (false === @chmod($path, 0644)) {
						throw new CsrException('Geen eigenaar van foto: ' . $path);
					}
				}
			} catch (Exception $e) {
				$errors++;
				if (defined('RESIZE_OUTPUT')) {
					debugprint($e->getMessage());
				} else {
					setMelding($e->getMessage(), -1);
				}
			}
		}
		$msg = <<<HTML
Voltooid met {$errors} errors. Dit album bevat {$albums} sub-albums en in totaal {$fotos} foto's.
HTML;
		if (defined('RESIZE_OUTPUT')) {
			echo '<br />' . $msg;
			exit();
		} else {
			setMelding($msg, $errors > 0 ? 2 : 1);
		}
	}

	public function getMostRecentFotoAlbum()
	{
		try {
			$album = $this->getFotoAlbum('');
			return $album->getMostRecentSubAlbum();
		} catch (NotFoundHttpException $ex) {
			return null;
		}
	}

	public function hernoemAlbum(FotoAlbum $album, $newName)
	{
		if (!valid_filename($newName)) {
			throw new CsrGebruikerException('Ongeldige naam');
		}
		// controleer rechten
		$oldDir = $album->subdir;
		if (false === @chmod(PHOTOALBUM_PATH . $oldDir, 0755)) {
			throw new CsrException(
				'Geen eigenaar van album: ' .
					htmlspecialchars(PHOTOALBUM_PATH . $oldDir)
			);
		}

		// nieuwe subdir op basis van path
		$newDir = dirname($oldDir) . '/' . $newName;

		if (is_dir(PHOTOALBUM_PATH . $newDir)) {
			throw new CsrException('Nieuwe album naam bestaat al');
		}

		if (false === @rename($album->path, PHOTOALBUM_PATH . $newDir)) {
			$error = error_get_last();
			throw new CsrException($error['message']);
		}
		// controleer rechten
		if (false === @chmod(PHOTOALBUM_PATH . $newDir, 0755)) {
			throw new CsrException(
				'Geen eigenaar van album: ' .
					htmlspecialchars(PHOTOALBUM_PATH . $newDir)
			);
		}

		// database in sync houden
		$album->dirname = basename($newDir);
		$album->subdir = $newDir;
		$album->path = PHOTOALBUM_PATH . $newDir;

		foreach ($this->findBySubdir($oldDir) as $subdir) {
			// updaten gaat niet vanwege primary key
			$this->delete($subdir);
			$subdir->subdir = str_replace($oldDir, $newDir, $album->subdir);
			$this->create($subdir);
		}
		foreach ($this->fotoRepository->findBySubdir($oldDir) as $foto) {
			/** @var Foto $foto */
			$oldUUID = $foto->getUUID();
			// updaten gaat niet vanwege primary key
			$this->fotoRepository->delete($foto);
			$foto->subdir = str_replace($oldDir, $newDir, $foto->subdir);
			$this->fotoRepository->create($foto);
			foreach (
				$this->fotoTagsRepository->findBy(['refuuid' => $oldUUID])
				as $tag
			) {
				// updaten gaat niet vanwege primary key
				$this->fotoTagsRepository->delete($tag);
				$tag->refuuid = $foto->getUUID();
				$this->fotoTagsRepository->create($tag);
			}
		}
		return true;
	}

	public function setAlbumCover(FotoAlbum $album, Foto $cover)
	{
		$success = true;
		// find old cover
		foreach ($album->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($foto->getFullPath() === $cover->getFullPath()) {
					$foto = $cover;
				}
				$path = $foto->getThumbPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getResizedPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getFullPath();
				$success &= rename($path, str_replace('folder', '', $path));
				if ($success) {
					// database in sync houden
					// updaten gaat niet vanwege primary key
					$this->fotoRepository->delete($foto);
					$foto->filename = str_replace('folder', '', $foto->filename);
					$this->fotoRepository->create($foto);
				}
				if ($foto === $cover) {
					return $success;
				}
			}
		}
		// set new cover
		$path = $cover->getThumbPath();
		$success &= rename(
			$path,
			substr_replace($path, 'folder', strrpos($path, '.'), 0)
		);
		$path = $cover->getResizedPath();
		$success &= rename(
			$path,
			substr_replace($path, 'folder', strrpos($path, '.'), 0)
		);
		$path = $cover->getFullPath();
		$success &= rename(
			$path,
			substr_replace($path, 'folder', strrpos($path, '.'), 0)
		);
		if ($success) {
			// database in sync houden
			// updaten gaat niet vanwege primary key
			$this->fotoRepository->delete($cover);
			$cover->filename = substr_replace(
				$cover->filename,
				'folder',
				strrpos($cover->filename, '.'),
				0
			);
			$this->fotoRepository->create($cover);
		}
		return $success;
	}

	public function opschonen(FotoAlbum $fotoalbum)
	{
		foreach ($this->findBySubdir($fotoalbum->subdir) as $album) {
			/** @var FotoAlbum $album */
			if (!$album->exists()) {
				foreach ($this->fotoRepository->findBySubdir($album->subdir) as $foto) {
					$this->fotoRepository->delete($foto);
					$this->fotoTagsRepository->verwijderFotoTags($foto);
				}
				$this->delete($album);
			}
		}
	}
}
