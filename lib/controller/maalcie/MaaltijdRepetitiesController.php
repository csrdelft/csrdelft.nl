<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\service\maalcie\MaaltijdRepetitiesService;
use CsrDelft\view\maalcie\forms\MaaltijdRepetitieForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaaltijdRepetitiesController extends AbstractController
{
	/** @var null MaaltijdRepetitie|null */
	private $repetitie = null;
	/** @var MaaltijdRepetitiesRepository */
	private $maaltijdRepetitiesRepository;
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/**
	 * @var MaaltijdRepetitiesService
	 */
	private $maaltijdRepetitiesService;

	public function __construct(
		MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository,
		MaaltijdRepetitiesService $maaltijdRepetitiesService,
		MaaltijdenRepository $maaltijdenRepository
	) {
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdRepetitiesService = $maaltijdRepetitiesService;
	}

	/**
	 * @param MaaltijdRepetitie|null $repetitie
	 * @return Response
	 * @Route("/maaltijden/repetities/{mlt_repetitie_id}", methods={"GET"}, defaults={"mlt_repetitie_id"=null})
	 * @Auth(P_MAAL_MOD)
	 */
	public function beheer(MaaltijdRepetitie $repetitie = null): Response
	{
		return $this->render(
			'maaltijden/maaltijdrepetitie/beheer_maaltijd_repetities.html.twig',
			[
				'repetities' => $this->maaltijdRepetitiesRepository->getAlleRepetities(),
				'modal' => $repetitie ? $this->bewerk($repetitie) : null,
			]
		);
	}

	/**
	 * @return MaaltijdRepetitieForm
	 * @Route("/maaltijden/repetities/nieuw", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function nieuw(): MaaltijdRepetitieForm
	{
		return new MaaltijdRepetitieForm(new MaaltijdRepetitie()); // fetches POST values itself
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return MaaltijdRepetitieForm
	 * @Route("/maaltijden/repetities/bewerk/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function bewerk(MaaltijdRepetitie $repetitie): MaaltijdRepetitieForm
	{
		return new MaaltijdRepetitieForm($repetitie); // fetches POST values itself
	}

	/**
	 * @param MaaltijdRepetitie|null $repetitie
	 * @return MaaltijdRepetitieForm|Response
	 * @throws Throwable
	 * @Route("/maaltijden/repetities/opslaan/{mlt_repetitie_id}", methods={"POST"}, defaults={"mlt_repetitie_id"=null})
	 * @Route("/maaltijden/repetities/opslaan/", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function opslaan(MaaltijdRepetitie $repetitie = null): Response|MaaltijdRepetitieForm
	{
		if ($repetitie) {
			$view = $this->bewerk($repetitie);
		} else {
			$view = $this->nieuw();
		}

		if ($view->validate()) {
			$repetitie = $view->getModel();

			$aantal = $this->maaltijdRepetitiesService->saveRepetitie($repetitie);
			if ($aantal > 0) {
				$this->addFlash(
					FlashType::WARNING,
					$aantal .
						' abonnement' .
						($aantal !== 1 ? 'en' : '') .
						' uitgeschakeld.'
				);
			}
			$this->repetitie = $repetitie;
			return $this->render(
				'maaltijden/maaltijdrepetitie/beheer_maaltijd_repetitie.html.twig',
				['repetitie' => $repetitie]
			);
		}

		return $view;
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws Throwable
	 * @Route("/maaltijden/repetities/verwijder/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function verwijder(MaaltijdRepetitie $repetitie): void
	{
		$aantal = $this->maaltijdRepetitiesService->verwijderRepetitie($repetitie);

		if ($aantal > 0) {
			$this->addFlash(
				FlashType::WARNING,
				$aantal .
					' abonnement' .
					($aantal !== 1 ? 'en' : '') .
					' uitgeschakeld.'
			);
		}

		echo '<tr id="maalcie-melding"><td>' .
			FlashUtil::getFlashUsingContainerFacade() .
			'</td></tr>';
		echo '<tr id="repetitie-row-' .
			$repetitie->mlt_repetitie_id .
			'" class="remove"></tr>';
		exit();
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return MaaltijdRepetitieForm
	 * @throws Throwable
	 * @Route("/maaltijden/repetities/bijwerken/{mlt_repetitie_id}", methods={"POST"})
	 * @Auth(P_MAAL_MOD)
	 */
	public function bijwerken(MaaltijdRepetitie $repetitie): MaaltijdRepetitieForm|Response
	{
		$view = $this->opslaan($repetitie);

		if ($this->repetitie) {
			// opslaan succesvol
			$verplaats = isset($_POST['verplaats_dag']);
			$updated_aanmeldingen = $this->maaltijdRepetitiesService->updateRepetitieMaaltijden(
				$this->repetitie,
				$verplaats
			);
			$this->addFlash(
				FlashType::SUCCESS,
				$updated_aanmeldingen[0] .
					' maaltijd' .
					($updated_aanmeldingen[0] !== 1 ? 'en' : '') .
					' bijgewerkt' .
					($verplaats ? ' en eventueel verplaatst.' : '.')
			);
			if ($updated_aanmeldingen[1] > 0) {
				$this->addFlash(
					FlashType::WARNING,
					$updated_aanmeldingen[1] .
						' aanmelding' .
						($updated_aanmeldingen[1] !== 1 ? 'en' : '') .
						' verwijderd vanwege aanmeldrestrictie: ' .
						$view->getModel()->abonnement_filter
				);
			}
		}

		return $view;
	}
}
