<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\service\corvee\CorveePuntenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BeheerPuntenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BeheerPuntenController extends AbstractController
{
	/**
	 * @var CorveeFunctiesRepository
	 */
	private $corveeFunctiesRepository;
	/**
	 * @var CorveePuntenService
	 */
	private $corveePuntenService;

	public function __construct(
		CorveeFunctiesRepository $corveeFunctiesRepository,
		CorveePuntenService $corveePuntenService
	) {
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveePuntenService = $corveePuntenService;
	}

	/**
	 * @return Response
	 * @Route("/corvee/punten", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function beheer()
	{
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$matrix = $this->corveePuntenService->loadPuntenVoorAlleLeden($functies);
		return $this->render('maaltijden/corveepunt/beheer_punten.html.twig', [
			'matrix' => $matrix,
			'functies' => $functies,
		]);
	}

	/**
	 * @param Profiel $profiel
	 * @return Response
	 * @Route("/corvee/punten/wijzigpunten/{uid}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function wijzigpunten(Profiel $profiel)
	{
		$punten = (int) filter_input(
			INPUT_POST,
			'totaal_punten',
			FILTER_SANITIZE_NUMBER_INT
		);
		$this->corveePuntenService->savePuntenVoorLid($profiel, $punten, null);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$corveePuntenOverzicht = $this->corveePuntenService->loadPuntenVoorLid(
			$profiel,
			$functies
		);
		return $this->render(
			'maaltijden/corveepunt/beheer_punten_lijst.html.twig',
			['puntenlijst' => $corveePuntenOverzicht]
		);
	}

	/**
	 * @param Profiel $profiel
	 * @return Response
	 * @Route("/corvee/punten/wijzigbonus/{uid}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function wijzigbonus(Profiel $profiel)
	{
		$bonus = (int) filter_input(
			INPUT_POST,
			'totaal_bonus',
			FILTER_SANITIZE_NUMBER_INT
		);
		$this->corveePuntenService->savePuntenVoorLid($profiel, null, $bonus);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$corveePuntenOverzicht = $this->corveePuntenService->loadPuntenVoorLid(
			$profiel,
			$functies
		);
		return $this->render(
			'maaltijden/corveepunt/beheer_punten_lijst.html.twig',
			['puntenlijst' => $corveePuntenOverzicht]
		);
	}

	/**
	 * @return Response
	 * @Route("/corvee/punten/resetjaar", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function resetjaar()
	{
		/**
		 * @var int $aantal
		 * @var int $taken
		 * @var CsrGebruikerException[] $errors
		 */
		list(
			$aantal,
			$taken,
			$errors,
		) = $this->corveePuntenService->resetCorveejaar();
		$view = $this->beheer();
		$this->addFlash(
			FlashType::SUCCESS,
			$aantal .
				' vrijstelling' .
				($aantal !== 1 ? 'en' : '') .
				' verwerkt en verwijderd'
		);
		$this->addFlash(
			FlashType::INFO,
			$taken .
				' ta' .
				($taken !== 1 ? 'ken' : 'ak') .
				' naar de prullenbak verplaatst'
		);
		foreach ($errors as $error) {
			$this->addFlash(FlashType::ERROR, $error->getMessage());
		}

		return $view;
	}
}
