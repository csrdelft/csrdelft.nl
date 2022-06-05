<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\service\corvee\CorveePuntenService;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnCorveeController extends AbstractController
{
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CorveeFunctiesRepository
	 */
	private $corveeFunctiesRepository;
	/**
	 * @var CorveeVrijstellingenRepository
	 */
	private $corveeVrijstellingenRepository;
	/**
	 * @var CorveePuntenService
	 */
	private $corveePuntenService;

	public function __construct(
		CorveeTakenRepository $corveeTakenRepository,
		CorveeVrijstellingenRepository $corveeVrijstellingenRepository,
		CorveeFunctiesRepository $corveeFunctiesRepository,
		CorveePuntenService $corveePuntenService
	) {
		$this->corveeVrijstellingenRepository = $corveeVrijstellingenRepository;
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveePuntenService = $corveePuntenService;
	}

	/**
	 * @return Response
	 * @Route("/corvee", methods={"GET"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function mijn()
	{
		$taken = $this->corveeTakenRepository->getKomendeTakenVoorLid(
			$this->getProfiel()
		);
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		$punten = $this->corveePuntenService->loadPuntenVoorLid(
			$this->getProfiel(),
			$functies
		);
		$vrijstelling = $this->corveeVrijstellingenRepository->getVrijstelling(
			$this->getUid()
		);
		return $this->render('maaltijden/corveetaak/mijn.html.twig', [
			'rooster' => $rooster,
			'functies' => $functies,
			'punten' => $punten,
			'vrijstelling' => $vrijstelling,
		]);
	}

	/**
	 * @return Response
	 * @Route("/corvee/rooster", methods={"GET"})
	 * @Auth(P_CORVEE_IK)
	 */
	public function rooster()
	{
		$taken = $this->corveeTakenRepository->getKomendeTaken();
		$toonverleden = LoginService::mag(P_CORVEE_MOD);
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		return $this->render('maaltijden/corveetaak/corvee_rooster.html.twig', [
			'rooster' => $rooster,
			'toonverleden' => $toonverleden,
		]);
	}

	/**
	 * @return Response
	 * @Route("/corvee/rooster/verleden", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function roosterVerleden()
	{
		$taken = $this->corveeTakenRepository->getVerledenTaken();
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		return $this->render('maaltijden/corveetaak/corvee_rooster.html.twig', [
			'rooster' => $rooster,
			'toonverleden' => false,
		]);
	}
}
