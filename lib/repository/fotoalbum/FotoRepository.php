<?php

namespace CsrDelft\repository\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class FotoRepository
 * @package CsrDelft\repository\fotoalbum
 * @method Foto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foto[]    findAll()
 * @method Foto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FotoRepository extends AbstractRepository
{
	/**
	 * @var FotoTagsRepository
	 */
	private $fotoTagsRepository;

	public function __construct(
		ManagerRegistry $registry,
		FotoTagsRepository $fotoTagsRepository
	) {
		parent::__construct($registry, Foto::class);

		$this->fotoTagsRepository = $fotoTagsRepository;
	}

	/**
	 * @override parent::retrieveByUUID($UUID)
	 */
	public function retrieveByUUID($UUID): ?object
	{
		$parts = explode('@', $UUID, 2);
		$path = explode('/', $parts[0]);
		$filename = array_pop($path);
		$subdir = implode('/', $path);
		return $this->find(['subdir' => $subdir, 'filename' => $filename]);
	}

	/**
	 * @param $subdir
	 * @param $filename
	 * @return Foto|null
	 */
	public function get($subdir, $filename)
	{
		return $this->find(['subdir' => $subdir, 'filename' => $filename]);
	}

	/**
	 * @param Foto $foto
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(Foto $foto)
	{
		$dbFoto = $this->find([
			'subdir' => $foto->subdir,
			'filename' => $foto->filename,
		]);
		if ($dbFoto) {
			$foto = $dbFoto;
		}

		$foto->owner = LoginService::getUid();
		$foto->owner_profiel = LoginService::getProfiel();
		$foto->rotation = 0;

		$this->getEntityManager()->persist($foto);
		$this->getEntityManager()->flush();
	}

	public function delete(Foto $foto)
	{
		// Sta toe om een detached foto entity te verwijderen.
		$this->createQueryBuilder('foto')
			->delete()
			->where('foto.subdir = :subdir and foto.filename = :filename')
			->setParameter('subdir', $foto->subdir)
			->setParameter('filename', $foto->filename)
			->getQuery()
			->execute();
	}

	/**
	 * @param Foto $foto
	 * @throws CsrException
	 */
	public function verwerkFoto(Foto $foto)
	{
		if (
			!$this->find(['subdir' => $foto->subdir, 'filename' => $foto->filename])
		) {
			$this->create($foto);
			if (false === @chmod($foto->getFullPath(), 0644)) {
				throw new CsrException(
					'Geen eigenaar van foto: ' . htmlspecialchars($foto->getFullPath())
				);
			}
		}
		if (!$foto->hasThumb()) {
			$foto->createThumb();
		}
		if (!$foto->hasResized()) {
			$foto->createResized();
		}
	}

	/**
	 * @param Foto $foto
	 * @return bool
	 */
	public function verwijderFoto(Foto $foto): int
	{
		$ret = true;
		$ret &= unlink($foto->getFullPath());
		if ($foto->hasResized()) {
			$ret &= unlink($foto->getResizedPath());
		}
		if ($foto->hasThumb()) {
			$ret &= unlink($foto->getThumbPath());
		}
		if ($ret) {
			$this->getEntityManager()->remove($foto);
			$this->getEntityManager()->flush();
			$this->fotoTagsRepository->verwijderFotoTags($foto);
		}
		return $ret;
	}

	/**
	 * Rotate resized & thumb for prettyPhoto to show the right way up.
	 *
	 * @param Foto $foto
	 * @param int $degrees
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function rotate(Foto $foto, $degrees)
	{
		$foto->rotation += $degrees;
		$foto->rotation %= 360;
		$this->getEntityManager()->persist($foto);
		$this->getEntityManager()->flush();

		if ($foto->hasThumb()) {
			unlink($foto->getThumbPath());
		}
		$foto->createThumb();

		if ($foto->hasResized()) {
			unlink($foto->getResizedPath());
		}
		$foto->createResized();
	}

	/**
	 * @param string|null $subdir
	 * @return Foto[]
	 */
	public function findBySubdir(?string $subdir)
	{
		return $this->createQueryBuilder('foto')
			->where('foto.subdir like :subdir')
			->setParameter('subdir', $subdir . '%')
			->getQuery()
			->getResult();
	}
}
