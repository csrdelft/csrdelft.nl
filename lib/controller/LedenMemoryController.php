<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\entity\groepen\Groep;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use const P_LEDEN_MOD;

class LedenMemoryController extends AbstractController
{
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
	public function memory(Request $request): Response
	{
		$lidstatus = array_merge(
			LidStatus::getLidLike(),
			LidStatus::getOudlidLike()
		);
		$lidstatus[] = LidStatus::Overleden;
		/** @var Profiel[] $leden */
		$leden = [];
		$cheat = $request->query->has('rosebud');
		$learnmode = $request->query->has('oefenen');
		$groep = $this->getVerticale($request) ?? $this->getLichting($request);
		if ($groep instanceof Verticale) {
			$titel =
				$groep->naam .
				' verticale ledenmemory' .
				($learnmode ? ' (oefenen)' : '');
		} elseif ($groep instanceof Lichting) {
			$titel =
				$groep->lidjaar .
				' lichting ledenmemory' .
				($learnmode ? ' (oefenen)' : '');
		} else {
			throw new CsrGebruikerException('Geen geldige groep');
		}
		if ($groep instanceof Groep) {
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
	 * @param Request $request
	 * @return Verticale|null
	 * @throws NonUniqueResultException
	 */
	private function getVerticale(Request $request): ?Verticale
	{
		$v = $request->query->get('verticale');
		if (!$v) {
			return null;
		}
		$verticale = false;
		if (strlen($v) == 1) {
			$verticale = $this->verticalenRepository->get($v);
		}
		if (!$verticale) {
			$verticale = $this->verticalenRepository
				->createQueryBuilder('v')
				->where('v.naam LIKE :naam')
				->setParameter('naam', SqlUtil::sql_contains($v))
				->setMaxResults(1)
				->getQuery()
				->getOneOrNullResult();
		}
		return $verticale ? $verticale : null;
	}

	/**
	 * @param Request $request
	 * @return Groep|null
	 */
	private function getLichting(Request $request)
	{
		$l = $request->query->getInt('lichting');
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
	public function memoryscore()
	{
		$score = $this->ledenMemoryScoresModel->nieuw();
		$form = new LedenMemoryScoreForm($score);
		if ($form->validate()) {
			$this->ledenMemoryScoresModel->create($score);
		}
		return new JsonResponse($score);
	}

	/**
	 * @param string $groepUuid
	 * @return LedenMemoryScoreResponse
	 * @Route("/leden/memoryscores/{groep}", methods={"POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function memoryscores($groepUuid = null)
	{
		$parts = explode('@', $groepUuid);
		if (isset($parts[0], $parts[1])) {
			if ($parts[1] == 'verticale.csrdelft.nl') {
				$groep = $this->verticalenRepository->retrieveByUUID($groepUuid);
			} elseif ($parts[1] == 'lichting.csrdelft.nl') {
				$groep = $this->lichtingenRepository->get($parts[0]);
			}
		}
		if (isset($groep)) {
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
	public function namenleren()
	{
		// Haal alle (adspirant-/gast-)leden op.
		$profielen = $this->profielRepository->findByLidStatus(
			LidStatus::getLidLike()
		);

		// Bouw infostructuur. array_values om array te resetten voor json_encode
		$leden = array_values(
			array_map(
				function ($profiel) {
					/** @var $profiel Profiel */
					return [
						'uid' => $profiel->uid,
						'voornaam' => $profiel->voornaam,
						'tussenvoegsel' => $profiel->tussenvoegsel,
						'achternaam' => $profiel->achternaam,
						'postfix' => $profiel->postfix,
						'lichting' => $profiel->lidjaar,
						'verticale' => $profiel->verticale
							? $profiel->getVerticale()->naam
							: 'Geen',
						'geslacht' => $profiel->geslacht->getValue(),
						'studie' => $profiel->studie,
					];
				},
				array_filter($profielen, function ($profiel) {
					$path = $profiel->getPasfotoInternalPath();
					return InstellingUtil::is_zichtbaar(
						$profiel,
						'profielfoto',
						'intern',
						P_LEDEN_MOD
					) && $path !== null;
				})
			)
		);

		// Laad Vue app.
		return $this->render('namenleren.html.twig', [
			'leden' => $leden,
		]);
	}
}
