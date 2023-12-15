<?php

namespace CsrDelft\service;

use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\agenda\AgendaVerbergenRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\service\maalcie\MaaltijdenService;
use CsrDelft\service\security\LoginService;
use DateTimeImmutable;
use Symfony\Component\Security\Core\Security;

class AgendaService
{
    /**
     * @var AgendaRepository
     */
    private $agendaRepository;
    /**
	 * @var AgendaVerbergenRepository
	 */
	private $agendaVerbergenRepository;
	/**
	 * @var ActiviteitenRepository
	 */
	private $activiteitenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var VerjaardagenService
	 */
	private $verjaardagenService;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var MaaltijdenService
	 */
	private $maaltijdenService;

    public function __construct(
        Security $security,
        AgendaRepository $agendaRepository,
		AgendaVerbergenRepository $agendaVerbergenRepository,
		ActiviteitenRepository $activiteitenRepository,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdenService $maaltijdenService,
		VerjaardagenService $verjaardagenService
    ) {
        $this->agendaRepository = $agendaRepository;
        $this->agendaVerbergenRepository = $agendaVerbergenRepository;
		$this->activiteitenRepository = $activiteitenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->verjaardagenService = $verjaardagenService;
		$this->security = $security;
		$this->maaltijdenService = $maaltijdenService;
    }

    /**
	 * Zoek in de activiteiten (titel en beschrijving) van vandaag
	 * naar het woord $woord, geef de eerste terug.
	 * @param $woord string
	 * @return Agendeerbaar|null
	 */
	public function zoekWoordAgenda($woord)
	{
		return $this->zoekRegexAgenda('/' . preg_quote($woord, '/') . '/iu');
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
				preg_match($patroon, $item->getTitel()) ||
				preg_match($patroon, $item->getBeschrijving())
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

    /**
	 * @param DateTimeImmutable $van
	 * @param DateTimeImmutable $tot
	 * @param bool $ical
	 * @param bool $zijbalk
	 * @return Agendeerbaar[]
	 */
	public function getAllAgendeerbaar(
		DateTimeImmutable $van,
		DateTimeImmutable $tot,
		$ical = false,
		$zijbalk = false
	) {
		$result = [];

		// AgendaItems
		/** @var AgendaItem[] $items */
		$items = $this->agendaRepository->getAgendaItems($van, $tot);
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
			!$zijbalk &&
			LoginService::mag(P_VERJAARDAGEN, $auth) &&
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
			$verborgen = $this->agendaVerbergenRepository->getVerborgen(LoginService::getUid(), array_keys($itemsByUUID));

			foreach ($verborgen as $verbergen) {
				unset($itemsByUUID[$verbergen->refuuid]);
			}
		}
		return $itemsByUUID;
	}
}