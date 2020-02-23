<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\commissievoorkeuren\AddCategorieFormulier;
use CsrDelft\view\commissievoorkeuren\AddCommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieFormulier;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurPraesesOpmerkingForm;
use Doctrine\ORM\EntityManagerInterface;


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

	public function overzicht() {
		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function commissie($commissieId) {
		$commissie = $this->getDoctrine()->getRepository(VoorkeurCommissie::class)->find($commissieId);

		return view('commissievoorkeuren.commissie', [
			'voorkeuren' => $this->commissieVoorkeurRepository->getVoorkeurenVoorCommissie($commissie),
			'commissie' => $commissie,
			'commissieFormulier' => new CommissieFormulier($commissie),
		]);
	}

	public function updatecommissie($commissieId) {
		$commissie = $this->getDoctrine()->getRepository(VoorkeurCommissie::class)->find($commissieId);
		$body = new CommissieFormulier($commissie);
		if ($body->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($commissie);
			$manager->flush();

			setMelding('Aanpassingen commissie opgeslagen', 1);
		}
		return $this->redirectToRoute('commissievoorkeuren-updatecommissie', ['commissieId' => $commissieId]);
	}

	public function nieuwecommissie(EntityManagerInterface $em) {
		$model = new VoorkeurCommissie();
		$form = new AddCommissieFormulier($model);

		if ($form->validate()) {
			$model->categorie = $em->getReference(VoorkeurCommissieCategorie::class, 1);
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();

			return $this->redirectToRoute('commissievoorkeuren-commissie', ['commissieId' => $model->id]);
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => $form,
			'categorieFormulier' => new AddCategorieFormulier(new VoorkeurCommissieCategorie()),
		]);
	}

	public function nieuwecategorie() {
		$model = new VoorkeurCommissieCategorie();
		$form = new AddCategorieFormulier($model);
		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($model);
			$manager->flush();
			return $this->redirectToRoute('commissievoorkeuren'); // Prevent resubmit
		}

		return view('commissievoorkeuren.overzicht', [
			'categorien' => $this->voorkeurCommissieRepository->getByCategorie(),
			'commissieFormulier' => new AddCommissieFormulier(new VoorkeurCommissie()),
			'categorieFormulier' => $form,
		]);
	}

	public function verwijdercategorie($categorieId) {
		$model = $this->getDoctrine()->getRepository(VoorkeurCommissieCategorie::class)->find($categorieId);

		if (count($model->commissies) == 0) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($model);
			$manager->flush();
			setMelding("Categorie '{$model->naam}' succesvol verwijderd", 1);

		} else {
			setMelding('Kan categorie niet verwijderen: is niet leeg', 2);
		}

		return $this->redirectToRoute('commissievoorkeuren');
	}

	public function lidpagina($uid) {
		if (!ProfielRepository::existsUid($uid)) {
			throw new CsrToegangException();
		}

		$profiel = ProfielRepository::get($uid);

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

		return view('commissievoorkeuren.profiel', [
			'profiel' => $profiel,
			'voorkeuren' => $voorkeurenMap,
			'commissies' => $commissies,
			'lidOpmerking' => $opmerking->lidOpmerking,
			'opmerkingForm' => new CommissieVoorkeurPraesesOpmerkingForm($opmerking)
		]);
	}

	public function lidpaginaopmerking($uid) {
		$opmerking = $this->getDoctrine()->getRepository(VoorkeurOpmerking::class)->find($uid);

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

		return $this->redirectToRoute('commissievoorkeuren-lidpagina-lijst');
	}
}
