<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\service\corvee\CorveePuntenService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MijnCorveeController extends AbstractController
{
	public function __construct(
		private readonly CorveeTakenRepository $corveeTakenRepository,
		private readonly CorveeVrijstellingenRepository $corveeVrijstellingenRepository,
		private readonly CorveeFunctiesRepository $corveeFunctiesRepository,
		private readonly CorveePuntenService $corveePuntenService
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_CORVEE_IK)
	 */
	#[Route(path: '/corvee', methods: ['GET'])]
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
	 * @Auth(P_CORVEE_IK)
	 */
	#[Route(path: '/corvee/rooster', methods: ['GET'])]
	public function rooster()
	{
		$taken = $this->corveeTakenRepository->getKomendeTaken();
		$toonverleden = $this->mag(P_CORVEE_MOD);
		$rooster = $this->corveeTakenRepository->getRoosterMatrix($taken);
		return $this->render('maaltijden/corveetaak/corvee_rooster.html.twig', [
			'rooster' => $rooster,
			'toonverleden' => $toonverleden,
		]);
	}

	/**
	 * @return Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/rooster/verleden', methods: ['GET'])]
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
