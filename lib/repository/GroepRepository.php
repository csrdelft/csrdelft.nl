<?php

namespace CsrDelft\repository;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepStatistiekDTO;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldLimiet;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

/**
 * AbstractGroepenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method Groep|null find($id, $lockMode = null, $lockVersion = null)
 * @method Groep|null findOneBy(array $criteria, array $orderBy = null)
 * @method Groep[]    findAll()
 * @method Groep|null retrieveByUUID($UUID)
 */
abstract class GroepRepository extends AbstractRepository
{


	/**
	 * @return Groep|string
	 */
	abstract public function getEntityClassName();

	public static function getUrl()
	{
		return '/groepen/' . static::getNaam();
	}

	public static function getNaam()
	{
		return strtolower(
			str_replace(
				'Repository',
				'',
				ReflectionUtil::classNameZonderNamespace(static::class)
			)
		);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param null $limit
	 * @param null $offset
	 * @return Groep[]
	 */
	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		// Eerst sorteren op FT/HT/OT
		$orderBy = ['status' => 'ASC'] + ($orderBy ?? []);
		if (
			in_array(
				HeeftMoment::class,
				class_implements($this->getEntityClassName())
			)
		) {
			// Als er een moment is daarna daarop sorteren
			$orderBy = ['beginMoment' => 'DESC'] + ($orderBy ?? []);
		}

		return parent::findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @param $id
	 * @return Groep|false
	 */
	public function get(int|null $id)
	{
		if (is_numeric($id)) {
			$groep = $this->find($id);

			if (!$groep) {
				return $this->findOneBy(['oudId' => $id]);
			}

			return $groep;
		}
		return $this->findOneBy(['familie' => $id, 'status' => GroepStatus::HT()]);
	}

	/**
	 * Set primary key.
	 *
	 * @param Groep $groep
	 * @return void
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(Groep $groep)
	{
		$this->_em->persist($groep);
		$this->_em->flush();
	}

	/**
	 * @param Groep $groep
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(Groep $groep)
	{
		$this->_em->persist($groep);
		$this->_em->flush();
	}

	/**
	 * @param Groep $groep
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function delete(Groep $groep)
	{
		$this->_em->remove($groep);
		$this->_em->flush();
	}

	/**
	 * @param null $soort
	 * @return Groep
	 */
	public function nieuw($soort = null)
	{
		$orm = $this->getClassName();
		$groep = new $orm();
		$groep->naam = null;
		$groep->familie = null;
		$groep->status = GroepStatus::HT();
		$groep->samenvatting = '';
		$groep->omschrijving = null;
		$groep->beginMoment = null;
		$groep->eindMoment = null;
		$groep->website = null;
		$groep->maker = LoginService::getProfiel();
		return $groep;
	}

	/**
	 * Return groepen by GroepStatus voor lid.
	 *
	 * @param string $uid
	 * @param GroepStatus|array $status
	 * @return Groep[]
	 */
	public function getGroepenVoorLid(Profiel $uid, $status = null)
	{
		$qb = $this->createQueryBuilder('ag');

		if (
			in_array(
				HeeftMoment::class,
				class_implements($this->getEntityClassName())
			)
		) {
			$qb = $qb->orderBy('ag.beginMoment', 'DESC');
		}

		$qb = $qb
			->join('ag.leden', 'l')
			->where('l.uid = :uid')
			->setParameter('uid', $uid->uid);

		if (is_array($status)) {
			$qb->andWhere('ag.status in (:status)')->setParameter('status', $status);
		} elseif ($status) {
			$qb->andWhere('ag.status = :status')->setParameter('status', $status);
		}

		return $qb->getQuery()->getResult();
	}

	/**
	 * Bereken statistieken van de groepleden.
	 *
	 * @param Groep $groep
	 * @return GroepStatistiekDTO
	 */
	public function getStatistieken(Groep $groep)
	{
		if ($groep->aantalLeden() == 0) {
			return new GroepStatistiekDTO(0, [], [], [], []);
		}

		$tijd = [];
		foreach ($groep->getLeden() as $groeplid) {
			$time = $groeplid->lidSinds->getTimestamp();
			if (isset($tijd[$time])) {
				$tijd[$time] += 1;
			} else {
				$tijd[$time] = 1;
			}
		}
		ksort($tijd);

		$totaal = $groep->aantalLeden();
		if ($groep instanceof HeeftAanmeldLimiet) {
			if ($groep->getAanmeldLimiet() === null) {
				$totaal .= ' (geen limiet)';
			} else {
				$totaal .= ' van ' . $groep->getAanmeldLimiet();
			}
		}

		$verticalen = $this->createQueryBuilder('g')
			->where('g.id = :id')
			->setParameter('id', $groep->id)
			->select('v.naam, count(p.uid) as aantal')
			->innerJoin('g.leden', 'l')
			->innerJoin('l.profiel', 'p')
			// v.letter is niet onderdeel van de pk van Verticale, dus een association is hier niet mogelijk
			->innerJoin(
				\CsrDelft\entity\groepen\Verticale::class,
				'v',
				Join::WITH,
				'v.letter = p.verticale'
			)
			->groupBy('p.verticale')
			->getQuery()
			->getArrayResult();

		$geslachten = $this->createQueryBuilder('g')
			->where('g.id = :id')
			->setParameter('id', $groep->id)
			->select('p.geslacht, COUNT(p.uid) as aantal')
			->innerJoin('g.leden', 'l')
			->innerJoin('l.profiel', 'p')
			->groupBy('p.geslacht')
			->getQuery()
			->getArrayResult();

		$lidjaren = $this->createQueryBuilder('g')
			->where('g.id = :id')
			->setParameter('id', $groep->id)
			->select('p.lidjaar, count(p.uid) as aantal')
			->innerJoin('g.leden', 'l')
			->innerJoin('l.profiel', 'p')
			->groupBy('p.lidjaar')
			->getQuery()
			->getArrayResult();

		return new GroepStatistiekDTO(
			$totaal,
			$verticalen,
			$geslachten,
			$lidjaren,
			$tijd
		);
	}

	/**
	 * @param string $zoekterm
	 * @param int $limit
	 * @param string[] $status
	 * @return Groep[]
	 */
	public function zoeken($zoekterm, $limit, $status)
	{
		foreach ($status as $item) {
			if (!GroepStatus::isValidValue($item)) {
				throw new \InvalidArgumentException(
					$item . ' is geen geldige groepstatus'
				);
			}
		}

		$query = $this->createQueryBuilder('g')
			->where('g.status IN (:status)')
			->setParameter('status', $status);

		if ($zoekterm != '') {
			$query = $query
				->andWhere('g.familie LIKE :zoekterm or g.naam LIKE :zoekterm')
				->setParameter('zoekterm', SqlUtil::sql_contains($zoekterm));
		}

		$query = $query->orderBy('g.id', 'DESC');

		$result = $query->getQuery()->iterate(null, AbstractQuery::HYDRATE_OBJECT);

		$num = 0;
		while ($num < $limit && ($object = $result->next()) !== false) {
			/** @var $object Groep[] */
			if (
				$this->security->isGranted(AbstractGroepVoter::BEKIJKEN, $object[0])
			) {
				$num++;
				yield $object[0];
			}
		}

		return [];
	}

	public function parseSoort(string $soort = null)
	{
		return null;
	}
}
