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
	 * @override parent::retrieveByUUID($UUID)
	 *
	 * @param null|scalar $UUID
	 */
	public function retrieveByUUID($UUID)
	{
		$parts = explode('@', (string) $UUID, 2);
		$path = explode('/', $parts[0]);
		$filename = array_pop($path);
		$subdir = implode('/', $path);
		return $this->find(['subdir' => $subdir, 'filename' => $filename]);
	}

	/**
	 * @param $subdir
	 * @param null|scalar $filename
	 *
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
}
