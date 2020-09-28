<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\LedenMemoryScoresRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\ledenmemory\LedenMemoryScoreForm;
use CsrDelft\view\ledenmemory\LedenMemoryScoreResponse;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LedenMemoryController extends AbstractController {
	/**
	 * @var LedenMemoryScoresRepository
	 */
	private $ledenMemoryScoresModel;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;
	/**
	 * @var LichtingenRepository
	 */
	private $lichtingenRepository;

	public function __construct(
		LedenMemoryScoresRepository $ledenMemoryScoresModel,
		ProfielRepository $profielRepository,
		VerticalenRepository $verticalenRepository,
		LichtingenRepository $lichtingenRepository
	) {
		$this->ledenMemoryScoresModel = $ledenMemoryScoresModel;
		$this->profielRepository = $profielRepository;
		$this->verticalenRepository = $verticalenRepository;
		$this->lichtingenRepository = $lichtingenRepository;
	}

	/**
	 * @return Response
	 * @throws NonUniqueResultException
	 * @Route("/leden/memory", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function memory() {
		$lidstatus = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$lidstatus[] = LidStatus::Overleden;
		/** @var Profiel[] $leden */
		$leden = [];
		$cheat = isset($_GET['rosebud']);
		$learnmode = isset($_GET['oefenen']);
		$groep = $this->getVerticale() ?? $this->getLichting();
		if ($groep instanceof Verticale) {
			$titel = $groep->naam . ' verticale ledenmemory' . ($learnmode ? ' (oefenen)' : '');
		} else if ($groep instanceof Lichting) {
			$titel = $groep->lidjaar . ' lichting ledenmemory' . ($learnmode ? ' (oefenen)' : '');
		} else {
			throw new CsrGebruikerException("Geen geldige groep");
		}
		if ($groep instanceof AbstractGroep) {
			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielRepository::get($lid->uid);
				if (in_array($profiel->status, $lidstatus)) {
					$leden[] = $profiel;
				}
			}
		}

		return $this->render('ledenmemory.html.twig', [
			'titel' => $titel,
			'groep' => $groep,
			'cheat' => $cheat,
			'learnmode' => $learnmode,
			'leden' => $leden,
		]);
	}

	/**
	 * @return Verticale|null
	 * @throws NonUniqueResultException
	 */
	private function getVerticale() {
		$v = filter_input(INPUT_GET, 'verticale', FILTER_SANITIZE_STRING);
		if (!$v) {
			return null;
		}
		$verticale = false;
		if (strlen($v) == 1) {
			$verticale = $this->verticalenRepository->get($v);
		}
		if (!$verticale) {
			$verticale = $this->verticalenRepository->createQueryBuilder('v')
				->where('v.naam LIKE :naam')
				->setParameter('naam', sql_contains($v))
				->setMaxResults(1)
				->getQuery()->getOneOrNullResult();
		}
		return $verticale ? $verticale : null;
	}

	/**
	 * @return AbstractGroep|null
	 */
	private function getLichting() {
		$l = (int)filter_input(INPUT_GET, 'lichting', FILTER_SANITIZE_NUMBER_INT);
		$min = LichtingenRepository::getOudsteLidjaar();
		$max = LichtingenRepository::getJongsteLidjaar();

		if ($l < $min || $l > $max) {
			$l = $max;
		}

		return $this->lichtingenRepository->get($l);
	}

	/**
	 * @return JsonResponse
	 * @Route("/leden/memoryscore", methods={"POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function memoryscore() {
		$score = $this->ledenMemoryScoresModel->nieuw();
		$form = new LedenMemoryScoreForm($score);
		if ($form->validate()) {
			$this->ledenMemoryScoresModel->create($score);
		}
		return new JsonResponse($score);
	}

	/**
	 * @param null $groep
	 * @return LedenMemoryScoreResponse
	 * @Route("/leden/memoryscores/{groep}", methods={"POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function memoryscores($groep = null) {
		$parts = explode('@', $groep);
		if (isset($parts[0], $parts[1])) {
			switch ($parts[1]) {
				case 'verticale.csrdelft.nl':
					$groep = $this->verticalenRepository->retrieveByUUID($groep);
					break;
				case 'lichting.csrdelft.nl':
					$groep = $this->lichtingenRepository->get($parts[0]);
					break;
			}
		}
		if ($groep) {
			$data = $this->ledenMemoryScoresModel->getGroepTopScores($groep);
		} else {
			$data = $this->ledenMemoryScoresModel->getAllTopScores();
		}
		return new LedenMemoryScoreResponse($data);
	}

	/**
	 * @return Response
	 * @Route("/leden/namen-leren", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function namenleren() {
		// Haal alle (adspirant-/gast-)leden op.
		$profielen = $this->profielRepository->findByLidStatus(LidStatus::getLidLike());

		// Bouw infostructuur.
		$leden = array_map(function ($profiel) {
			/** @var $profiel Profiel */
			return [
				'uid' => $profiel->uid,
				'voornaam' => $profiel->voornaam,
				'tussenvoegsel' => $profiel->tussenvoegsel,
				'achternaam' => $profiel->achternaam,
				'lichting' => $profiel->lidjaar,
				'verticale' => $profiel->verticale ? $profiel->getVerticale()->naam : 'Geen',
				'geslacht' => $profiel->geslacht->getValue(),
				'studie' => $profiel->studie,
			];
		}, array_filter($profielen, function ($profiel) {
			$path = $profiel->getPasfotoInternalPath();
			return
				is_zichtbaar($profiel, 'profielfoto', 'intern') &&
				$path !== null;
		}));

		// Laad Vue app.
		return $this->render('namenleren.html.twig', ['leden' => json_encode($leden)]);
	}
}
