<?php

namespace CsrDelft\repository\agenda;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\AgendaVerbergen;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\service\maalcie\MaaltijdenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 *
 * @method AgendaItem find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaItem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRepository extends AbstractRepository
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		ManagerRegistry $registry,
		Security $security,
	) {
		parent::__construct($registry, AgendaItem::class);

		$this->security = $security;
	}

	/**
	 * Vergelijkt twee Agendeerbaars op beginMoment t.b.v. sorteren.
	 * @param Agendeerbaar $foo
	 * @param Agendeerbaar $bar
	 * @return int
	 */
	public static function vergelijkAgendeerbaars(
		Agendeerbaar $foo,
		Agendeerbaar $bar
	) {
		$a = $foo->getBeginMoment();
		$b = $bar->getBeginMoment();
		if ($a > $b) {
			return 1;
		} elseif ($a < $b) {
			return -1;
		} else {
			return 0;
		}
	}

	/**
	 * @param $itemId
	 * @return AgendaItem|null
	 */
	public function getAgendaItem($itemId)
	{
		return $this->find($itemId);
	}

	/**
	 * @param DateTimeImmutable $van
	 * @param DateTimeImmutable $tot
	 * @param $query
	 * @param $limiet
	 * @return AgendaItem[]
	 */
	public function zoeken(
		DateTimeImmutable $van,
		DateTimeImmutable $tot,
		$query,
		$limiet
	) {
		return $this->createQueryBuilder('a')
			->where('a.eind_moment >= :van and a.begin_moment <= :tot')
			->andWhere(
				'a.titel like :query or a.beschrijving like :query or a.locatie like :query'
			)
			->setParameter('van', $van, Types::DATE_IMMUTABLE)
			->setParameter('tot', $tot, Types::DATE_IMMUTABLE)
			->setParameter('query', SqlUtil::sql_contains($query))
			->orderBy('a.begin_moment', 'ASC')
			->addOrderBy('a.titel', 'ASC')
			->setMaxResults($limiet)
			->getQuery()
			->getResult();
	}

	/**
	 * @return AgendaItem[]
	 */
	public function getAgendaItems(DateTimeImmutable $van, DateTimeImmutable $tot)
	{
		return $this->createQueryBuilder('a')
			->where(
				'a.begin_moment >= :begin_moment and a.begin_moment < :eind_moment'
			)
			->orWhere(
				'a.eind_moment >= :begin_moment and a.eind_moment < :eind_moment'
			)
			->setParameter('begin_moment', $van, Types::DATE_IMMUTABLE)
			->setParameter(
				'eind_moment',
				$tot->add(DateInterval::createFromDateString('1 day')),
				Types::DATE_IMMUTABLE
			)
			->getQuery()
			->getResult();
	}

	public function nieuw($beginMoment, $eindMoment)
	{
		$item = new AgendaItem();
		$item->begin_moment = $beginMoment
			? date_create_immutable($beginMoment)
			: date_create_immutable()->add(new DateInterval('P1D'));
		$item->eind_moment = $eindMoment
			? date_create_immutable($eindMoment)
			: date_create_immutable()->add(new DateInterval('P2D'));
		if (LoginService::mag(P_AGENDA_MOD)) {
			$item->rechten_bekijken = InstellingUtil::instelling(
				'agenda',
				'standaard_rechten'
			);
		} else {
			$item->rechten_bekijken =
				'verticale:' . $this->security->getUser()->profiel->verticale;
		}
		return $item;
	}
}
