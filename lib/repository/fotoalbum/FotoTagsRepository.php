<?php

namespace CsrDelft\repository\fotoalbum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoTag;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package CsrDelft\repository\fotoalbum
 * @method FotoTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method FotoTag|null findOneBy(array $criteria, array $orderBy = null)
 */
class FotoTagsRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, FotoTag::class);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @return FotoTag[]
	 */
	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		if (!$orderBy) {
			$orderBy = ['wanneer' => 'DESC'];
		}
		return parent::findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @return FotoTag[]
	 */
	public function findAll()
	{
		return parent::findBy([]);
	}

	public function getTags(Foto $foto)
	{
		return $this->findBy(['refuuid' => $foto->getUUID()]);
	}

	public function addTag(Foto $foto, $uid, $x, $y, $size)
	{
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrGebruikerException('Profiel bestaat niet');
		}

		$tag = $this->find(['refuuid' => $foto->getUUID(), 'keyword' => $uid]) ?? new FotoTag();

		$tag->refuuid = $foto->getUUID();
		$tag->keyword = $uid;
		$tag->door = LoginService::getUid();
		$tag->wanneer = date_create_immutable();
		$tag->x = (int)$x;
		$tag->y = (int)$y;
		$tag->size = (int)$size;

		$this->getEntityManager()->persist($tag);
		$this->getEntityManager()->flush();

		return $tag;
	}

	public function removeTag(
		$refuuid,
		$keyword
	)
	{
		$tag = $this->find(['refuuid' => $refuuid, 'keyword' => $keyword]);
		if ($tag) {
			$this->getEntityManager()->remove($tag);
			$this->getEntityManager()->flush();
		}
	}

	public function verwijderFotoTags(Foto $foto)
	{
		$this->createQueryBuilder('t')
			->delete()
			->where('t.refuuid = :refuuid')
			->setParameter('refuuid', $foto->getUUID())
			->getQuery()->execute();
	}

	public function create(FotoTag $tag)
	{
		$this->getEntityManager()->persist($tag);
		$this->getEntityManager()->flush();
	}

	public function delete(FotoTag $tag)
	{
		$this->getEntityManager()->remove($tag);
		$this->getEntityManager()->flush();
	}

}
