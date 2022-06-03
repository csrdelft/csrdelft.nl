<?php

namespace CsrDelft\repository;

use CsrDelft\entity\ForumPlaatje;
use CsrDelft\view\formulier\uploadvelden\ImageField;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;


/**
 * Class ForumPlaatjeRepository
 * @package CsrDelft\repository
 * @method ForumPlaatje|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumPlaatje|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumPlaatje[]    findAll()
 * @method ForumPlaatje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumPlaatjeRepository extends AbstractRepository
{

	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ManagerRegistry $registry, ProfielRepository $profielRepository)
	{
		parent::__construct($registry, ForumPlaatje::class);
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @param ImageField $uploader
	 * @param $uid
	 * @return ForumPlaatje
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function fromUploader(ImageField $uploader, $uid)
	{
		$plaatje = static::generate();
		$plaatje->maker = $uid;
		$plaatje->maker_profiel = $this->profielRepository->find($uid);

		$this->getEntityManager()->persist($plaatje);
		$this->getEntityManager()->flush();

		$uploader->opslaan(PLAATJES_PATH, strval($plaatje->id));
		$plaatje->createResized();
		return $plaatje;
	}

	private static function generate()
	{
		$plaatje = new ForumPlaatje();
		$plaatje->datum_toegevoegd = date_create_immutable();
		$plaatje->access_key = bin2hex(random_bytes(16));
		return $plaatje;
	}

	/**
	 * @param $key
	 * @return ForumPlaatje|null
	 */
	public function getByKey($key)
	{
		if (!self::isValidKey($key)) {
			return null;
		}
		return $this->findOneBy(["access_key" => $key]);

	}

	public static function isValidKey($key)
	{
		return preg_match('/^[a-zA-Z0-9]{32}$/', $key);
	}

}
