<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\Streeplijst;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\StreeplijstRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\streeplijst\StreeplijstForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \CsrDelft\view\ToHtmlResponse;


/**
 * StreeplijstController.class.php
 *
 * @author J. de Jong
 *
 * Controller voor streeplijst generator
 */
class StreeplijstController extends AbstractController
{
	/**
	 * @var StreeplijstRepository
	 */
	private $streeplijstRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;

	public function __construct(
		StreeplijstRepository $streeplijstRepository, ProfielRepository $profielRepository, VerticalenRepository $verticalenRepository)
	{
		$this->streeplijstRepository = $streeplijstRepository;
		$this->profielRepository = $profielRepository;
		$this->verticalenRepository = $verticalenRepository;
	}

	/**
	 * @return \CsrDelft\view\renderer\TemplateView
	 * @Route("/streeplijst", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function overzicht()
	{
		return view('streeplijst.overzicht', [
			'streeplijstoverzicht' => $this->streeplijstRepository->getAlleStreeplijsten(),
			'streeplijstformulier' => new StreeplijstForm(new Streeplijst()),
			'huidigestreeplijst' => new Streeplijst(),
			'verticalen' => $this->verticalenRepository->findAll(),
			'jongstelidjaar' => LichtingenRepository::getJongsteLidjaar(),

		]);

	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Route("/streeplijst/aanmaken", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aanmaken()
	{
		$nieuwelijst = $this->streeplijstRepository->nieuw($_GET["naam_streeplijst"], $_GET["leden_streeplijst"], $_GET["inhoud_streeplijst"]);
		if ($nieuwelijst) $manager = $this->getDoctrine()->getManager();
		$manager->persist($nieuwelijst);
		$manager->flush();
		return $this->redirectToRoute('csrdelft_streeplijst_overzicht');
	}

	/**
	 * @return StreeplijstForm
	 * @Route("/streeplijst/bewerken/{id}", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bewerken($id)
	{
		// haalt naam, inhoud en leden uit de database
		$streeplijst = $this->getDoctrine()->getRepository(Streeplijst::class)->find($id);

		// return deze dingen in de tekstboxen.
		return view('streeplijst.overzicht', [
			'streeplijstoverzicht' => $this->streeplijstRepository->getAlleStreeplijsten(),
			'huidigestreeplijst' => $streeplijst,
			'streeplijstformulier' => new StreeplijstForm(new Streeplijst()),
			'verticalen' => $this->verticalenRepository->findAll(),
			'jongstelidjaar' => LichtingenRepository::getJongsteLidjaar(),
		]);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Route("/streeplijst/verwijderen/{id}", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen($id)
	{
		$streeplijst = $this->getDoctrine()->getRepository(Streeplijst::class)->find($id);
		$manager = $this->getDoctrine()->getManager();
		$manager->remove($streeplijst);
		$manager->flush();
		return $this->redirectToRoute('csrdelft_streeplijst_overzicht');
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Route("/streeplijst/selectie", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function selectie(Request $request)
	{
		$verticale = $request->request->get('verticale');
		$lichting = $request->request->get('lichting');
		$ledentype = $request->request->get('ledentype');
		$criteria = [];
//		$criteria = ['status' => LidStatus::getLidLike()];

		$criteria = ['status' => $ledentype];
		if ($verticale && $verticale != 'alle') {
			$criteria['verticale'] = $verticale;
		}
		if ($lichting && $lichting != 'alle') {
			$criteria['lidjaar'] = $lichting;
		}
		$profielen = $this->profielRepository->findBy($criteria);
		$namen = [];
		$naamopmaak = $request->request->get('naamopmaak');
		if ($naamopmaak == 'Achternaam') {
			foreach ($profielen as $profiel) {
				$namen[] = $profiel->getNaam('streeplijst');
			}
		} elseif ($naamopmaak == 'Civitas') {
			foreach ($profielen as $profiel) {
				$namen[] = $profiel->getNaam('civitas');
			}
		} else {
			foreach ($profielen as $profiel) {
				$namen[] = $profiel->getNaam();
			}
		}
		// Hoe krijg ik een array van alle checked dingen, ipv enkel de laatste?
		// Of gewoon per stuk opvragen?
		$goederen = $request->request->get('streepbareUnits');
		$opmaakInhoud = $request->request->get('opmaakInhoud');
		if ($opmaakInhoud == true) {
			sort($goederen);
		}
		$stringGoederen = null;
		if ($goederen != null) {
			$stringGoederen = implode("; ", $goederen);
		}

		$opmaakAbc = $request->request->get('opmaakabc');
		if ($opmaakAbc == true) {
			sort($namen);
		}
		$stringNamen = implode("; ", $namen);

		$streeplijst = new Streeplijst();
		$streeplijst->leden_streeplijst = $stringNamen;
		$streeplijst->inhoud_streeplijst = $stringGoederen;


		return view('streeplijst.overzicht', [
			'streeplijstoverzicht' => $this->streeplijstRepository->getAlleStreeplijsten(),
			'huidigestreeplijst' => $streeplijst,
			'streeplijstformulier' => new StreeplijstForm(new Streeplijst()),
			'verticalen' => $this->verticalenRepository->findAll(),
			'jongstelidjaar' => LichtingenRepository::getJongsteLidjaar(),

		]);
	}

	/**
	 * @return ???
	 * @Route("/streeplijst/genereren/{id}", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function genereren($id)
	{
		$streeplijst = $this->getDoctrine()->getRepository(Streeplijst::class)->find($id);

		return view('streeplijst.streeplijst', [
			'streeplijst' => $streeplijst
		]);
	}

	/**
	 * @return  ???
	 * @Route("/streeplijst/genererenZonderId", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function genererenZonderId()
	{
		$nieuwelijst = $this->streeplijstRepository->nieuw($_GET["naam_streeplijst"], $_GET["leden_streeplijst"], $_GET["inhoud_streeplijst"]);

		return view('streeplijst.streeplijst', [
			'streeplijst' => $nieuwelijst
		]);
	}


}
