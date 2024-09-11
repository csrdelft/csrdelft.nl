<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\repository\corvee\CorveeKwalificatiesRepository;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\maalcie\corvee\functies\FunctieDeleteView;
use CsrDelft\view\maalcie\corvee\functies\FunctieForm;
use CsrDelft\view\maalcie\corvee\functies\KwalificatieForm;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerFunctiesController extends AbstractController
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly CorveeFunctiesRepository $corveeFunctiesRepository,
		private readonly CorveeKwalificatiesRepository $corveeKwalificatiesRepository
	) {
	}

	/**
	 * @param Request $request
	 * @return GenericSuggestiesResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/corvee/functies/suggesties',
			methods: ['GET'],
			options: ['priority' => 1]
		)
	]
	public function suggesties(Request $request)
	{
		return new GenericSuggestiesResponse(
			$this->corveeFunctiesRepository->getSuggesties($request->query->get('q'))
		);
	}

	/**
	 * @param CorveeFunctie|null $functie
	 * @return Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[
		Route(
			path: '/corvee/functies/{functie_id}',
			methods: ['GET'],
			defaults: ['functie_id' => null]
		)
	]
	public function beheer(CorveeFunctie $functie = null)
	{
		$modal = $functie ? $this->bewerken($functie) : null;
		$functies = $this->corveeFunctiesRepository->getAlleFuncties(); // grouped by functie_id
		return $this->render('maaltijden/functie/beheer_functies.html.twig', [
			'functies' => $functies,
			'modal' => $modal,
		]);
	}

	/**
	 * @return FunctieForm|Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/functies/toevoegen', methods: ['POST'])]
	public function toevoegen()
	{
		$functie = $this->corveeFunctiesRepository->nieuw();
		$form = new FunctieForm($functie, 'toevoegen'); // fetches POST values itself
		if ($form->validate()) {
			$this->entityManager->persist($functie);
			$this->entityManager->flush();

			$this->addFlash(FlashType::SUCCESS, 'Toegevoegd');

			return $this->render('maaltijden/functie/beheer_functie.html.twig', [
				'functie' => $functie,
			]);
		} else {
			return $form;
		}
	}

	/**
	 * @param CorveeFunctie $functie
	 * @return FunctieForm|Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/functies/bewerken/{functie_id}', methods: ['POST'])]
	public function bewerken(CorveeFunctie $functie)
	{
		$form = new FunctieForm($functie, 'bewerken'); // fetches POST values itself
		if ($form->validate()) {
			$this->entityManager->flush();
			$this->addFlash(FlashType::SUCCESS, 'Bijgewerkt');
			return $this->render('maaltijden/functie/beheer_functie.html.twig', [
				'functie' => $functie,
			]);
		} else {
			// Voorkom opslaan
			$this->entityManager->clear();
			return $form;
		}
	}

	/**
	 * @param CorveeFunctie $functie
	 * @return FunctieDeleteView
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/functies/verwijderen/{functie_id}', methods: ['POST'])]
	public function verwijderen(CorveeFunctie $functie)
	{
		$functieId = $functie->functie_id;
		$this->corveeFunctiesRepository->removeFunctie($functie);
		$this->addFlash(FlashType::SUCCESS, 'Verwijderd');
		return new FunctieDeleteView($functieId);
	}

	/**
	 * @param CorveeFunctie $functie
	 * @return KwalificatieForm|Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/functies/kwalificeer/{functie_id}', methods: ['POST'])]
	public function kwalificeer(CorveeFunctie $functie)
	{
		$kwalificatie = $this->corveeKwalificatiesRepository->nieuw($functie);
		$form = new KwalificatieForm($kwalificatie); // fetches POST values itself
		if ($form->validate()) {
			$this->corveeKwalificatiesRepository->kwalificatieToewijzen(
				$kwalificatie
			);
			return $this->render('maaltijden/functie/beheer_functie.html.twig', [
				'functie' => $functie,
			]);
		} else {
			return $form;
		}
	}

	/**
	 * @param CorveeKwalificatie $kwalificatie
	 * @return Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[
		Route(
			path: '/corvee/functies/dekwalificeer/{functie_id}/{uid}',
			methods: ['POST']
		)
	]
	public function dekwalificeer(CorveeKwalificatie $kwalificatie)
	{
		$functie = $kwalificatie->corveeFunctie;
		$this->entityManager->remove($kwalificatie);
		$this->entityManager->flush();

		return $this->render('maaltijden/functie/beheer_functie.html.twig', [
			'functie' => $functie,
		]);
	}
}
