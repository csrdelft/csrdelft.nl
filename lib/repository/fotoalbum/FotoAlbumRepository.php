<?php

namespace CsrDelft\repository\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Security\Voter\Entity\FotoAlbumVoter;
use CsrDelft\common\Util\DebugUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\common\Util\PathUtil;
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

	public function getFotoAlbum(string $path)
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

	public function getMostRecentFotoAlbum()
	{
		try {
			$album = $this->getFotoAlbum('');
			return $album->getMostRecentSubAlbum();
		} catch (NotFoundHttpException) {
			return null;
		}
	}
}
