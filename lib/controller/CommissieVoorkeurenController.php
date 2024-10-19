<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\FlashType;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\commissievoorkeuren\VoorkeurCommissieCategorieType;
use CsrDelft\view\commissievoorkeuren\VoorkeurCommissieType;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingType;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CommissieVoorkeurenController.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor commissie voorkeuren.
 */
class CommissieVoorkeurenController extends AbstractController
{
	public function __construct(
		private readonly CommissieVoorkeurRepository $commissieVoorkeurRepository,
		private readonly VoorkeurCommissieRepository $voorkeurCommissieRepository,
		private readonly VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth({"bestuur",P_ADMIN})
	 */
	#[Route(path: '/commissievoorkeuren', methods: ['GET'])]
	public function overzicht(): Response
	{
		$commissieFormulier = $this->createForm(
			VoorkeurCommissieType::class,
			new VoorkeurCommissie(),
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecommissie'
				),
			]
		);

		$addCategorieFormulier = $this->createForm(
			VoorkeurCommissieCategorieType::class,
			new VoorkeurCommissieCategorie(),
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecategorie'
				),
			]
		);

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $addCategorieFormulier->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param $cid
	 * @param $waarde
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/commissievoorkeuren/update/{cid}/{uid}/{waarde}',
			methods: ['POST'],
			defaults: ['waarde' => null]
		)
	]
	public function lidUpdate(
		Request $request,
		ProfielRepository $profielRepository,
		$cid,
		$uid,
		$waarde
	) {
		$profiel = $profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}
		if (!$profiel->magBewerken()) {
			throw $this->createAccessDeniedException();
		}

		if ($waarde === null) {
			$waarde = $request->request->get('waarde');
		}

		if (!is_numeric($waarde) || intval($waarde) > 3 || intval($waarde) < 1) {
			return new JsonResponse(['success' => false], 400);
		}

		$commissie = $this->voorkeurCommissieRepository->find($cid);
		$commissieVoorkeur = $this->commissieVoorkeurRepository->getVoorkeur(
			$profiel,
			$commissie
		);
		$commissieVoorkeur->voorkeur = $waarde;
		$commissieVoorkeur->timestamp = date_create_immutable();

		$this->getDoctrine()
			->getManager()
			->flush();

		return new JsonResponse(['success' => true]);
	}

	/**
	 * @param VoorkeurCommissie $commissie
	 * @return Response
	 * @Auth({"bestuur",P_ADMIN})
	 */
	#[Route(path: '/commissievoorkeuren/overzicht/{id}', methods: ['GET'])]
	public function commissie(VoorkeurCommissie $commissie): Response
	{
		$form = $this->createForm(VoorkeurCommissieType::class, $commissie);

		return $this->render('commissievoorkeuren/commissie.html.twig', [
			'voorkeuren' => $this->commissieVoorkeurRepository->getVoorkeurenVoorCommissie(
				$commissie
			),
			'commissie' => $commissie,
			'commissieFormulier' => $form->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param VoorkeurCommissie $commissie
	 * @return RedirectResponse
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	#[Route(path: '/commissievoorkeuren/overzicht/{id}', methods: ['POST'])]
	public function updatecommissie(
		Request $request,
		VoorkeurCommissie $commissie
	): RedirectResponse {
		$form = $this->createForm(VoorkeurCommissieType::class, $commissie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($commissie);
			$manager->flush();

			$this->addFlash(FlashType::SUCCESS, 'Aanpassingen commissie opgeslagen');
		}
		return $this->redirectToRoute(
			'csrdelft_commissievoorkeuren_updatecommissie',
			['id' => $commissie->id]
		);
	}

	/**
	 * @return Response
	 * @throws ORMException
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	#[Route(path: '/commissievoorkeuren/nieuwecommissie', methods: ['POST'])]
	public function nieuwecommissie(Request $request): Response
	{
		$model = new VoorkeurCommissie();
		$commissieFormulier = $this->createForm(
			VoorkeurCommissieType::class,
			$model,
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecommissie'
				),
			]
		);
		$commissieFormulier->handleRequest($request);

		if ($commissieFormulier->isSubmitted() && $commissieFormulier->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();

			return $this->redirectToRoute('csrdelft_commissievoorkeuren_commissie', [
				'id' => $model->id,
			]);
		}

		$categorieFormulier = $this->createForm(
			VoorkeurCommissieCategorieType::class,
			new VoorkeurCommissieCategorie(),
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecategorie'
				),
			]
		);

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $categorieFormulier->createView(),
		]);
	}

	/**
	 * @return Response
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	#[Route(path: '/commissievoorkeuren/nieuwecategorie', methods: ['POST'])]
	public function nieuwecategorie(Request $request): Response
	{
		$model = new VoorkeurCommissieCategorie();
		$categorieFormulier = $this->createForm(
			VoorkeurCommissieCategorieType::class,
			$model,
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecategorie'
				),
			]
		);
		$categorieFormulier->handleRequest($request);

		if ($categorieFormulier->isSubmitted() && $categorieFormulier->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_commissievoorkeuren_overzicht'); // Prevent resubmit
		}

		$commissieFormulier = $this->createForm(
			VoorkeurCommissieType::class,
			new VoorkeurCommissie(),
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_nieuwecommissie'
				),
			]
		);
		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $categorieFormulier->createView(),
		]);
	}

	/**
	 * @param VoorkeurCommissieCategorie $categorie
	 * @return RedirectResponse
	 * @Auth({"bestuur",P_ADMIN})
	 */
	#[
		Route(
			path: '/commissievoorkeuren/verwijdercategorie/{id}',
			methods: ['POST']
		)
	]
	public function verwijdercategorie(
		VoorkeurCommissieCategorie $categorie
	): RedirectResponse {
		if (count($categorie->commissies) == 0) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($categorie);
			$manager->flush();
			$this->addFlash(
				FlashType::SUCCESS,
				"Categorie '{$categorie->naam}' succesvol verwijderd"
			);
		} else {
			$this->addFlash(
				FlashType::WARNING,
				'Kan categorie niet verwijderen: is niet leeg'
			);
		}

		return $this->redirectToRoute('csrdelft_commissievoorkeuren_overzicht');
	}

	/**
	 * @param Profiel $profiel
	 * @return Response
	 * @Auth({"bestuur",P_ADMIN})
	 */
	#[Route(path: '/commissievoorkeuren/lidpagina/{uid}', methods: ['GET'])]
	public function lidpagina(Profiel $profiel): Response
	{
		$voorkeuren = $this->commissieVoorkeurRepository->getVoorkeurenVoorLid(
			$profiel
		);
		$voorkeurenMap = [];
		$commissies = $this->voorkeurCommissieRepository->findBy([
			'zichtbaar' => 'true',
		]);
		foreach ($commissies as $commissie) {
			$voorkeurenMap[$commissie->id] = null;
		}
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}

		$opmerking = $this->voorkeurOpmerkingRepository->getOpmerkingVoorLid(
			$profiel
		);

		$commissieVoorkeurPraesesOpmerkingForm = $this->createForm(
			CommissieVoorkeurPraesesOpmerkingType::class,
			$opmerking,
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_lidpaginaopmerking',
					['uid' => $profiel->uid]
				),
			]
		);
		return $this->render('commissievoorkeuren/profiel.html.twig', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => $commissieVoorkeurPraesesOpmerkingForm->createView(),
		]);
	}

	/**
	 * @param $uid
	 * @param VoorkeurOpmerking|null $opmerking
	 * @return RedirectResponse
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	#[Route(path: '/commissievoorkeuren/lidpagina/{uid}', methods: ['POST'])]
	public function lidpaginaopmerking(
		Request $request,
		$uid,
		VoorkeurOpmerking $opmerking = null
	): RedirectResponse {
		if (!$opmerking instanceof VoorkeurOpmerking) {
			$opmerking = new VoorkeurOpmerking();
			$opmerking->uid = $uid;
		}

		$form = $this->createForm(
			CommissieVoorkeurPraesesOpmerkingType::class,
			$opmerking,
			[
				'action' => $this->generateUrl(
					'csrdelft_commissievoorkeuren_lidpaginaopmerking',
					['uid' => $uid]
				),
			]
		);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($opmerking);
			$manager->flush();
		}

		return $this->redirectToRoute('csrdelft_commissievoorkeuren_lidpagina', [
			'uid' => $uid,
		]);
	}
}
