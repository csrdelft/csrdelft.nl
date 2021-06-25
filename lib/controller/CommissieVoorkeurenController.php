<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\view\commissievoorkeuren\AddCategorieFormulier;
use CsrDelft\view\commissievoorkeuren\AddCommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingForm;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
class CommissieVoorkeurenController extends AbstractController {
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
	) {
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
		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
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
		return $this->render('commissievoorkeuren/commissie.html.twig', [
			'voorkeuren' => $this->commissieVoorkeurRepository->getVoorkeurenVoorCommissie($commissie),
			'commissie' => $commissie,
			'commissieFormulier' => new CommissieFormulier($commissie),
		]);
	}

	/**
	 * @param VoorkeurCommissie $commissie
	 * @return RedirectResponse
	 * @Route("/commissievoorkeuren/overzicht/{id}", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function updatecommissie(VoorkeurCommissie $commissie): RedirectResponse
	{
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($commissie);
			$manager->flush();

			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		return $this->redirectToRoute('csrdelft_commissievoorkeuren_updatecommissie', ['id' => $commissie->id]);
	}

	/**
	 * @param EntityManagerInterface $em
	 * @return Response
	 * @throws ORMException
	 * @Route("/commissievoorkeuren/nieuwecommissie", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function nieuwecommissie(EntityManagerInterface $em): Response
	{
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);

		if ($form->validate()) {
			$model->categorie = $em->getReference(VoorkeurCommissieCategorie::class, 1);
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();

			return $this->redirectToRoute('csrdelft_commissievoorkeuren_commissie', ['id' => $model->id]);
		}

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $form,
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	/**
	 * @return Response
	 * @Route("/commissievoorkeuren/nieuwecategorie", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function nieuwecategorie(): Response
	{
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_commissievoorkeuren_overzicht'); // Prevent resubmit
		}

		return $this->render('commissievoorkeuren/overzicht.html.twig', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => $form,
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

		return $this->render('commissievoorkeuren/profiel.html.twig', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => new CommissieVoorkeurPraesesOpmerkingForm($opmerking)
		]);
	}

	/**
	 * @param $uid
	 * @param VoorkeurOpmerking|null $opmerking
	 * @return RedirectResponse
	 * @Route("/commissievoorkeuren/lidpagina/{uid}", methods={"POST"})
	 * @Auth({"bestuur",P_ADMIN})
	 */
	public function lidpaginaopmerking($uid, VoorkeurOpmerking $opmerking = null): RedirectResponse
	{
		if (!$opmerking) {
			$opmerking = new VoorkeurOpmerking();
			$opmerking->uid = $uid;
		}

		$form = new CommissieVoorkeurPraesesOpmerkingForm($opmerking);

		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($opmerking);
			$manager->flush();
		}

		return $this->redirectToRoute('csrdelft_commissievoorkeuren_lidpagina', ['uid' => $uid]);
	}
}
