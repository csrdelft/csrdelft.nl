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
	public function __construct(
		ManagerRegistry $registry,
		private readonly Security $security,
		private readonly AgendaVerbergenRepository $agendaVerbergenRepository,
		private readonly ActiviteitenRepository $activiteitenRepository,
		private readonly CorveeTakenRepository $corveeTakenRepository,
		private readonly MaaltijdenService $maaltijdenService,
		private readonly VerjaardagenService $verjaardagenService
	) {
		parent::__construct($registry, AgendaItem::class);
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

	public function getICalendarItems()
	{
		return $this->filterVerborgen(
			$this->getAllAgendeerbaar(
				date_create_immutable(
					InstellingUtil::instelling('agenda', 'ical_from')
				),
				date_create_immutable(InstellingUtil::instelling('agenda', 'ical_to')),
				true
			)
		);
	}

	public function filterVerborgen(array $items)
	{
		// Items verbergen
		$itemsByUUID = [];
		foreach ($items as $index => $item) {
			$itemsByUUID[$item->getUUID()] = $item;
			unset($items[$index]);
		}
		if (!empty($itemsByUUID)) {
			/** @var AgendaVerbergen[] $verborgen */
			$verborgen = $this->agendaVerbergenRepository
				->createQueryBuilder('av')
				->where('av.uid = :uid and av.refuuid in (:uuids)')
				->setParameter('uid', LoginService::getUid())
				->setParameter('uuids', array_keys($itemsByUUID))
				->getQuery()
				->getResult();

			foreach ($verborgen as $verbergen) {
				unset($itemsByUUID[$verbergen->refuuid]);
			}
		}
		return $itemsByUUID;
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
	 * @param DateTimeImmutable $van
	 * @param DateTimeImmutable $tot
	 * @param bool $ical
	 * @param bool $voorpagina
	 * @return Agendeerbaar[]
	 */
	public function getAllAgendeerbaar(
		DateTimeImmutable $van,
		DateTimeImmutable $tot,
		$ical = false,
		$voorpagina = false
	) {
		$result = [];

		// AgendaItems
		/** @var AgendaItem[] $items */
		$items = $this->createQueryBuilder('a')
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
		foreach ($items as $item) {
			if ($item->magBekijken($ical)) {
				$result[] = $item;
			}
		}

		$auth = $ical ? AuthenticationMethod::getEnumValues() : null;

		// Activiteiten
		/** @var Activiteit[] $activiteiten */
		$activiteiten = $this->activiteitenRepository->getGroepenVoorAgenda(
			$van,
			$tot
		);
		foreach ($activiteiten as $activiteit) {
			if (
				$this->security->isGranted(AbstractGroepVoter::BEKIJKEN, $activiteit)
			) {
				$result[] = $activiteit;
			}
		}

		// Maaltijden
		if (InstellingUtil::lid_instelling('agenda', 'toonMaaltijden') === 'ja') {
			// TODO: Dit moet altijd aanstaan
			$result = array_merge(
				$result,
				$this->maaltijdenService->getMaaltijdenVoorAgenda(
					$van->getTimestamp(),
					$tot->getTimestamp()
				)
			);
		}

		// CorveeTaken
		if (InstellingUtil::lid_instelling('agenda', 'toonCorvee') === 'iedereen') {
			$result = array_merge(
				$result,
				$this->corveeTakenRepository->getTakenVoorAgenda($van, $tot, true)
			);
		} elseif (
			InstellingUtil::lid_instelling('agenda', 'toonCorvee') === 'eigen'
		) {
			$result = array_merge(
				$result,
				$this->corveeTakenRepository->getTakenVoorAgenda($van, $tot, false)
			);
		}

		// Verjaardagen
		$toonVerjaardagen = $ical ? 'toonVerjaardagenICal' : 'toonVerjaardagen';
		if (
			!$voorpagina &&
			LoginService::mag(P_VERJAARDAGEN) &&
			InstellingUtil::lid_instelling('agenda', $toonVerjaardagen) === 'ja'
		) {
			//Verjaardagen. Omdat Lid-objectjes eigenlijk niet Agendeerbaar, maar meer iets als
			//PeriodiekAgendeerbaar zijn, maar we geen zin hebben om dat te implementeren,
			//doen we hier even een vieze hack waardoor het wel soort van werkt.
			$GLOBALS['agenda_van'] = $van;
			$GLOBALS['agenda_tot'] = $tot;

			$result = array_merge(
				$result,
				$this->verjaardagenService->getTussen($van, $tot)
			);
		}

		// Sorteren
		usort($result, [AgendaRepository::class, 'vergelijkAgendeerbaars']);

		return $result;
	}

	/**
	 * Zoek in de activiteiten (titel en beschrijving) van vandaag
	 * naar het woord $woord, geef de eerste terug.
	 * @param $woord string
	 * @return Agendeerbaar|null
	 */
	public function zoekWoordAgenda($woord)
	{
		return $this->zoekRegexAgenda(
			'/' . preg_quote((string) $woord, '/') . '/iu'
		);
	}

	/**
	 * Vind de eerste activiteit van vandaag waarvan de
	 * titel of omschrijving wordt gematcht door $patroon.
	 * @param $patroon string
	 * @return Agendeerbaar|null
	 */
	public function zoekRegexAgenda($patroon)
	{
		$beginDag = date_create_immutable('today');
		foreach ($this->getItemsByDay($beginDag) as $item) {
			if (
				preg_match($patroon, (string) $item->getTitel()) ||
				preg_match($patroon, (string) $item->getBeschrijving())
			) {
				return $item;
			}
		}
		return null;
	}

	public function getItemsByDay(DateTimeImmutable $dag)
	{
		return $this->getAllAgendeerbaar($dag, $dag);
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
