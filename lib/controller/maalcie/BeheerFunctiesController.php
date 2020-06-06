<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeKwalificatiesRepository;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\maalcie\corvee\functies\FunctieDeleteView;
use CsrDelft\view\maalcie\corvee\functies\FunctieForm;
use CsrDelft\view\maalcie\corvee\functies\KwalificatieForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerFunctiesController {
	/** @var CorveeFunctiesRepository */
	private $corveeFunctiesRepository;
	/** @var CorveeKwalificatiesRepository */
	private $corveeKwalificatiesRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager, CorveeFunctiesRepository $corveeFunctiesRepository, CorveeKwalificatiesRepository $corveeKwalificatiesRepository) {
		$this->corveeFunctiesRepository = $corveeFunctiesRepository;
		$this->corveeKwalificatiesRepository = $corveeKwalificatiesRepository;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Request $request
	 * @return GenericSuggestiesResponse
	 * @Route("/corvee/functies/suggesties", methods={"GET"}, options={"priority"=1})
	 * @Auth(P_LOGGED_IN)
	 */
	public function suggesties(Request $request) {
		return new GenericSuggestiesResponse($this->corveeFunctiesRepository->getSuggesties($request->query->get('q')));
	}

	/**
	 * @param CorveeFunctie|null $functie
	 * @return TemplateView
	 * @Route("/corvee/functies/{functie_id}", methods={"GET"}, defaults={"functie_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function beheer(CorveeFunctie $functie = null) {
		$modal = $functie ? $this->bewerken($functie) : null;
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		return view('maaltijden.functie.beheer_functies', ['functies' => $functies, 'modal' => $modal]);
	}

	/**
	 * @return FunctieForm|TemplateView
	 * @Route("/corvee/functies/toevoegen", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function toevoegen() {
		$functie = $this->corveeFunctiesRepository->nieuw();
		$form = new FunctieForm($functie, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$this->entityManager->persist($functie);
			$this->entityManager->flush();

			setMelding('Toegevoegd', 1);

			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			return $form;
		}
	}

	/**
	 * @param CorveeFunctie $functie
	 * @return FunctieForm|TemplateView
	 * @Route("/corvee/functies/bewerken/{functie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function bewerken(CorveeFunctie $functie) {
		$form = new FunctieForm($functie, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$this->entityManager->flush();
			setMelding('Bijgewerkt', 1);
			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			// Voorkom opslaan
			$this->entityManager->clear();
			return $form;
		}
	}

	/**
	 * @param CorveeFunctie $functie
	 * @return FunctieDeleteView
	 * @Route("/corvee/functies/verwijderen/{functie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function verwijderen(CorveeFunctie $functie) {
		$functieId = $functie->functie_id;
		$this->corveeFunctiesRepository->removeFunctie($functie);
		setMelding('Verwijderd', 1);
		return new FunctieDeleteView($functieId);
	}

	/**
	 * @param $functie_id
	 * @return KwalificatieForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/functies/kwalificeer/{functie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function kwalificeer(CorveeFunctie $functie) {
		$kwalificatie = $this->corveeKwalificatiesRepository->nieuw($functie);
		$form = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($form->validate()) {
			$this->corveeKwalificatiesRepository->kwalificatieToewijzen($kwalificatie);
			return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
		} else {
			return $form;
		}
	}

	/**
	 * @param CorveeKwalificatie $kwalificatie
	 * @return TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/functies/dekwalificeer/{functie_id}/{uid}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function dekwalificeer(CorveeKwalificatie $kwalificatie) {
		$functie = $kwalificatie->corveeFunctie;
		$this->entityManager->remove($kwalificatie);
		$this->entityManager->flush();

		return view('maaltijden.functie.beheer_functie', ['functie' => $functie]);
	}
}
