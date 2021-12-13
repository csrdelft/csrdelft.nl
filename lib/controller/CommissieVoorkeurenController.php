<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\view\commissievoorkeuren\VoorkeurCommissieCategorieType;
use CsrDelft\view\commissievoorkeuren\VoorkeurCommissieType;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingType;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
	/**
	 * @var CommissieVoorkeurRepository
	 */
	private $commissieVoorkeurRepository;
	/**
	 * @var VoorkeurCommissieRepository
	 */
	private $voorkeurCommissieRepository;
	/**
	 * @var VoorkeurOpmerkingRepository
	 */
	private $voorkeurOpmerkingRepository;

	public function __construct(
		CommissieVoorkeurRepository $commissieVoorkeurRepository,
		VoorkeurCommissieRepository $voorkeurCommissieRepository,
		VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository
	)
	{
		$this->commissieVoorkeurRepository = $commissieVoorkeurRepository;
		$this->voorkeurCommissieRepository = $voorkeurCommissieRepository;
		$this->voorkeurOpmerkingRepository = $voorkeurOpmerkingRepository;
	}

	/**
	 * @return Response
	 * @Route("/commissievoorkeuren", methods={"GET"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function overzicht(): Response
	{
		$commissieFormulier = $this->createForm(VoorkeurCommissieType::class, new VoorkeurCommissie(), [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecommissie'),
		]);

		$addCategorieFormulier = $this->createForm(VoorkeurCommissieCategorieType:: class, new VoorkeurCommissieCategorie(), [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecategorie')
		]);

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $addCategorieFormulier->createView(),
		]);
	}

	/**
	 * @param VoorkeurCommissie $commissie
	 * @return Response
	 * @Route("/commissievoorkeuren/overzicht/{id}", methods={"GET"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function commissie(VoorkeurCommissie $commissie): Response
	{
		$form = $this->createForm(VoorkeurCommissieType::class, $commissie);

		return $this->render('commissievoorkeuren/commissie.html.twig', [
			'voorkeuren' => $this->commissieVoorkeurRepository->getVoorkeurenVoorCommissie($commissie),
			'commissie' => $commissie,
			'commissieFormulier' => $form->createView(),
		]);
	}

	/**
	 * @param Request $request
	 * @param VoorkeurCommissie $commissie
	 * @return RedirectResponse
	 * @Route("/commissievoorkeuren/overzicht/{id}", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function updatecommissie(Request $request, VoorkeurCommissie $commissie): RedirectResponse
	{
		$form = $this->createForm(VoorkeurCommissieType::class, $commissie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($commissie);
			$manager->flush();

			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		return $this->redirectToRoute('csrdelft_commissievoorkeuren_updatecommissie', ['id' => $commissie->id]);
	}

	/**
	 * @return Response
	 * @throws ORMException
	 * @Route("/commissievoorkeuren/nieuwecommissie", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	public function nieuwecommissie(Request $request): Response
	{
		$model = new VoorkeurCommissie();
		$commissieFormulier = $this->createForm(VoorkeurCommissieType::class, $model, [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecommissie')
		]);
		$commissieFormulier->handleRequest($request);

		if ($commissieFormulier->isSubmitted() && $commissieFormulier->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();

			return $this->redirectToRoute('csrdelft_commissievoorkeuren_commissie', ['id' => $model->id]);
		}

		$categorieFormulier = $this->createForm(VoorkeurCommissieCategorieType::class, new VoorkeurCommissieCategorie(), [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecategorie')
		]);

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $categorieFormulier->createView(),
		]);
	}

	/**
	 * @return Response
	 * @Route("/commissievoorkeuren/nieuwecategorie", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 * @CsrfUnsafe
	 */
	public function nieuwecategorie(Request $request): Response
	{
		$model = new VoorkeurCommissieCategorie();
		$categorieFormulier = $this->createForm(VoorkeurCommissieCategorieType::class, $model, [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecategorie'),
		]);
		$categorieFormulier->handleRequest($request);

		if ($categorieFormulier->isSubmitted() && $categorieFormulier->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_commissievoorkeuren_overzicht'); // Prevent resubmit
		}

		$commissieFormulier = $this->createForm(VoorkeurCommissieType::class, new VoorkeurCommissie(), [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_nieuwecommissie'),
		]);
		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $commissieFormulier->createView(),
			'categorieFormulier' => $categorieFormulier->createView(),
		]);
	}

	/**
	 * @param VoorkeurCommissieCategorie $categorie
	 * @return RedirectResponse
	 * @Route("/commissievoorkeuren/verwijdercategorie/{id}", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function verwijdercategorie(VoorkeurCommissieCategorie $categorie): RedirectResponse
	{
		if (count($categorie->commissies) == 0) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($categorie);
			$manager->flush();
			setMelding("Categorie '{$categorie->naam}' succesvol verwijderd", 1);

		} else {
			setMelding('Kan categorie niet verwijderen: is niet leeg', 2);
		}

		return $this->redirectToRoute('csrdelft_commissievoorkeuren_overzicht');
	}

	/**
	 * @param Profiel $profiel
	 * @return Response
	 * @Route("/commissievoorkeuren/lidpagina/{uid}", methods={"GET"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function lidpagina(Profiel $profiel): Response
	{
		$voorkeuren = $this->commissieVoorkeurRepository->getVoorkeurenVoorLid($profiel);
		$voorkeurenMap = [];
		$commissies = $this->voorkeurCommissieRepository->findBy(['zichtbaar' => 'true']);
		foreach ($commissies as $commissie) {
			$voorkeurenMap[$commissie->id] = null;
		}
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}

		$opmerking = $this->voorkeurOpmerkingRepository->getOpmerkingVoorLid($profiel);

		$commissieVoorkeurPraesesOpmerkingForm = $this->createForm(CommissieVoorkeurPraesesOpmerkingType::class, $opmerking, [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_lidpaginaopmerking', ['uid' => $profiel->uid]),
		]);
		return $this->render('commissievoorkeuren/profiel.html.twig', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => $commissieVoorkeurPraesesOpmerkingForm->createView()
		]);
	}

	/**
	 * @param $uid
	 * @param VoorkeurOpmerking|null $opmerking
	 * @return RedirectResponse
	 * @Route("/commissievoorkeuren/lidpagina/{uid}", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function lidpaginaopmerking(Request $request, $uid, VoorkeurOpmerking $opmerking = null): RedirectResponse
	{
		if (!$opmerking) {
			$opmerking = new VoorkeurOpmerking();
			$opmerking->uid = $uid;
		}

		$form = $this->createForm(CommissieVoorkeurPraesesOpmerkingType::class, $opmerking, [
			'action' => $this->generateUrl('csrdelft_commissievoorkeuren_lidpaginaopmerking', ['uid' => $uid]),
		]);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($opmerking);
			$manager->flush();
		}

		return $this->redirectToRoute('csrdelft_commissievoorkeuren_lidpagina', ['uid' => $uid]);
	}
}
