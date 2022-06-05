<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\view\maalcie\forms\CorveeRepetitieForm;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class CorveeRepetitiesController extends AbstractController
{
	private $repetitie = null;
	/**
	 * @var CorveeRepetitiesRepository
	 */
	private $corveeRepetitiesRepository;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		CorveeRepetitiesRepository $corveeRepetitiesRepository,
		MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository,
		CorveeTakenRepository $corveeTakenRepository,
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository
	) {
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param CorveeRepetitie|null $corveeRepetitie
	 * @param MaaltijdRepetitie|null $maaltijdRepetitie
	 * @return Response
	 * @Route("/corvee/repetities/{crv_repetitie_id<\d*>}/{mlt_repetitie_id<\d*>}", methods={"GET"}, defaults={"crv_repetitie_id"=null,"mlt_repetitie_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function beheer(
		CorveeRepetitie $corveeRepetitie = null,
		MaaltijdRepetitie $maaltijdRepetitie = null
	) {
		$modal = null;
		if ($corveeRepetitie) {
			$modal = $this->bewerk($corveeRepetitie);
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		} elseif ($maaltijdRepetitie) {
			$repetities = $this->corveeRepetitiesRepository->getRepetitiesVoorMaaltijdRepetitie(
				$maaltijdRepetitie->mlt_repetitie_id
			);
		} else {
			$repetities = $this->corveeRepetitiesRepository->getAlleRepetities();
		}
		return $this->render(
			'maaltijden/corveerepetitie/beheer_corvee_repetities.html.twig',
			[
				'repetities' => $repetities,
				'maaltijdrepetitie' => $maaltijdRepetitie,
				'modal' => $modal,
			]
		);
	}

	/**
	 * @param MaaltijdRepetitie $maaltijdRepetitie
	 * @return Response
	 * @Route("/corvee/repetities/maaltijd/{mlt_repetitie_id<\d+>}", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function maaltijd(MaaltijdRepetitie $maaltijdRepetitie)
	{
		return $this->beheer(null, $maaltijdRepetitie);
	}

	/**
	 * @param MaaltijdRepetitie|null $repetitie
	 * @return CorveeRepetitieForm
	 * @Route("/corvee/repetities/nieuw/{mlt_repetitie_id<\d*>}", methods={"POST"}, defaults={"mlt_repetitie_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function nieuw(MaaltijdRepetitie $repetitie = null)
	{
		$repetitie = $this->corveeRepetitiesRepository->nieuw($repetitie);
		return new CorveeRepetitieForm($repetitie); // fetches POST values itself
	}

	/**
	 * @param CorveeRepetitie $corveeRepetitie
	 * @return CorveeRepetitieForm
	 * @Route("/corvee/repetities/bewerk/{crv_repetitie_id<\d+>}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function bewerk(CorveeRepetitie $corveeRepetitie)
	{
		return new CorveeRepetitieForm($corveeRepetitie); // fetches POST values itself
	}

	/**
	 * @param CorveeRepetitie|null $corveeRepetitie
	 * @return CorveeRepetitieForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/repetities/opslaan/{crv_repetitie_id<\d*>}", methods={"POST"}, defaults={"crv_repetitie_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function opslaan(CorveeRepetitie $corveeRepetitie = null)
	{
		if ($corveeRepetitie) {
			$view = $this->bewerk($corveeRepetitie);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$repetitie = $view->getModel();

			// Voor bijwerken
			$this->repetitie = $repetitie;
			if (!empty($repetitie->mlt_repetitie_id)) {
				$repetitie->maaltijdRepetitie = $this->entityManager
					->getRepository(MaaltijdRepetitie::class)
					->find($repetitie->mlt_repetitie_id);
			} else {
				$repetitie->maaltijdRepetitie = null;
			}

			$this->entityManager->persist($repetitie);
			$this->entityManager->flush();

			if (!$repetitie->voorkeurbaar) {
				// niet (meer) voorkeurbaar
				$aantal = $this->corveeVoorkeurenRepository->verwijderVoorkeuren(
					$corveeRepetitie->crv_repetitie_id
				);

				if ($aantal > 0) {
					setMelding(
						$aantal .
							' voorkeur' .
							($aantal !== 1 ? 'en' : '') .
							' uitgeschakeld.',
						2
					);
				}
			}

			return $this->render(
				'maaltijden/corveerepetitie/beheer_corvee_repetitie.html.twig',
				['repetitie' => $repetitie]
			);
		}

		return $view;
	}

	/**
	 * @param CorveeRepetitie $corveeRepetitie
	 * @Route("/corvee/repetities/verwijder/{crv_repetitie_id<\d+>}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function verwijder(CorveeRepetitie $corveeRepetitie)
	{
		$aantal = $this->corveeRepetitiesRepository->verwijderRepetitie(
			$corveeRepetitie->crv_repetitie_id
		);
		if ($aantal > 0) {
			setMelding(
				$aantal . ' voorkeur' . ($aantal !== 1 ? 'en' : '') . ' uitgeschakeld.',
				2
			);
		}
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		echo '<tr id="repetitie-row-' .
			$corveeRepetitie->crv_repetitie_id .
			'" class="remove"></tr>';
		exit();
	}

	/**
	 * @param CorveeRepetitie $corveeRepetitie
	 * @return CorveeRepetitieForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws Throwable
	 * @Route("/corvee/repetities/bijwerken/{crv_repetitie_id<\d+>}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function bijwerken(CorveeRepetitie $corveeRepetitie)
	{
		$view = $this->opslaan($corveeRepetitie);

		if ($this->repetitie) {
			// Opslaan gelukt
			$verplaats = isset($_POST['verplaats_dag']);
			$aantal = $this->corveeTakenRepository->updateRepetitieTaken(
				$this->repetitie,
				$verplaats
			);
			if ($aantal->update < $aantal->day) {
				$aantal->update = $aantal->day;
			}
			setMelding(
				$aantal->update .
					' corveeta' .
					($aantal->update !== 1 ? 'ken' : 'ak') .
					' bijgewerkt waarvan ' .
					$aantal->day .
					' van dag verschoven.',
				1
			);
			$aantal->datum += $aantal->maaltijd;
			setMelding(
				$aantal->datum .
					' corveeta' .
					($aantal->datum !== 1 ? 'ken' : 'ak') .
					' aangemaakt waarvan ' .
					$aantal->maaltijd .
					' maaltijdcorvee.',
				1
			);
		}

		return $view;
	}
}
