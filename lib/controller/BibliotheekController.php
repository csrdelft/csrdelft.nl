<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\bibliotheek\BiebRubriekRepository;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\BoekImporter;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatable;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatableResponse;
use CsrDelft\view\bibliotheek\BoekExemplaarFormulier;
use CsrDelft\view\bibliotheek\BoekFormulier;
use CsrDelft\view\bibliotheek\BoekRecensieFormulier;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\Icon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BibliotheekController.class.php  |  Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends AbstractController {
	/**
	 * @var BoekExemplaarRepository
	 */
	private $boekExemplaarRepository;
	/**
	 * @var BoekRepository
	 */
	private $boekRepository;
	/**
	 * @var BoekRecensieRepository
	 */
	private $boekRecensieRepository;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;
	/**
	 * @var BiebRubriekRepository
	 */
	private $biebRubriekRepository;

	public function __construct(
		BoekExemplaarRepository $boekExemplaarRepository,
		BoekRepository $boekRepository,
		BoekRecensieRepository $boekRecensieRepository,
		BiebRubriekRepository $biebRubriekRepository,
		CmsPaginaRepository $cmsPaginaRepository
	) {
		$this->boekExemplaarRepository = $boekExemplaarRepository;
		$this->boekRepository = $boekRepository;
		$this->boekRecensieRepository = $boekRecensieRepository;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
		$this->biebRubriekRepository = $biebRubriekRepository;
	}

	/**
	 * @param Request $request
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/boek/{boek}/recensie", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function recensie(Request $request, Boek $boek) {
		$recensie = $this->boekRecensieRepository->get($boek, $this->getProfiel());
		$formulier = $this->createFormulier(BoekRecensieFormulier::class, $recensie);
		$formulier->handleRequest($request);
		if ($formulier->validate()) {
			if (!$recensie->magBewerken()) {
				throw $this->createAccessDeniedException("Mag recensie niet bewerken");
			} else {
				$recensie->bewerkdatum = date_create_immutable();
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($recensie);
				$manager->flush();
				setMelding("Recensie opgeslagen", 0);
			}
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $boek->id]);
	}

	/**
	 * @return Response
	 * @Route("/bibliotheek/rubrieken", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function rubrieken() {
		return $this->render('default.html.twig', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('rubrieken'))]);
	}

	/**
	 * @return Response
	 * @Route("/bibliotheek/wenslijst", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function wenslijst() {
		return $this->render('default.html.twig', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('wenslijst'))]);
	}

	/**
	 * @Route("/bibliotheek", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function catalogustonen() {
		return $this->render('default.html.twig', ['content' => new BibliotheekCatalogusDatatable()]);
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 * @Route("/bibliotheek/catalogusdata", methods={"POST"})
	 * @Auth(P_BIEB_READ)
	 * @param Request $request
	 * @return BibliotheekCatalogusDatatableResponse
	 */
	public function catalogusdata(Request $request) {
		$boeken = $this->boekRepository->findAll();
		$uid = $request->query->get("eigenaar");
		$results = [];
		if ($uid !== null) {
			foreach ($boeken as $boek) {
				if ($boek->isEigenaar($uid)) {
					$results[] = $boek;
				}
			}
		} else {
			$results = $boeken;
		}
		return new BibliotheekCatalogusDatatableResponse($results);
	}

	/**
	 * Boek weergeven
	 * @param Request $request
	 * @param Boek|null $boek
	 * @return Response
	 * @Route("/bibliotheek/boek/{boek}", methods={"GET", "POST"}, defaults={"boek": null}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function boek(Request $request, Boek $boek = null) {
		if ($boek == null) {
			$boek = new Boek();
		}
		$boekForm = $this->createFormulier(BoekFormulier::class, $boek);
		$boekForm->handleRequest($request);
		if ($boekForm->validate()) {
			if (!$boek->magBewerken()) {
				throw $this->createAccessDeniedException('U mag dit boek niet bewerken');
			} else {
				$boek->setCategorie($this->biebRubriekRepository->find($boek->categorie_id));
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($boek);
				$manager->flush();

				return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $boek->id]);
			}
		}

		$alleRecensies = $boek->getRecensies();
		/** @var BoekRecensie[] $andereRecensies */
		$andereRecensies = [];
		$mijnRecensie = new BoekRecensie();
		$mijnRecensie->boek = $boek;
		/** @var Response[] $exemplaarFormulieren */
		$exemplaarFormulieren = [];
		foreach ($boek->getExemplaren() as $exemplaar) {
			if ($exemplaar->magBewerken()) {
				$exemplaarFormulieren[$exemplaar->id] = $this->createFormulier(BoekExemplaarFormulier::class, $exemplaar)->createView();
			}
		}
		foreach ($alleRecensies as $recensie) {
			if ($recensie->schrijver_uid == $this->getUid()) {
				$mijnRecensie = $recensie;
			}
			$andereRecensies[] = $recensie;

		}
		return $this->render('bibliotheek/boek.html.twig', [
			'boek' => $boek,
			'recensies' => $andereRecensies,
			'boekFormulier' => $boekForm->createView(),
			'mijnRecensie' => $mijnRecensie,
			'recensieFormulier' => $boek->id ? $this->createFormulier(BoekRecensieFormulier::class, $mijnRecensie)->createView() : null,
			'exemplaarFormulieren' => $exemplaarFormulieren,
		]);
	}

	/**
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/import/{boek}", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function import(Boek $boek) {
		if (!$boek->isEigenaar()) {
			throw $this->createAccessDeniedException();
		} else {
			$importer = new BoekImporter();
			$importer->import($boek);
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($boek);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $boek->id]);
		}
	}

	/**
	 * @param Boek $boek
	 * @param Profiel $profiel
	 * @Route("/bibliotheek/verwijderbeschrijving/{boek}/{profiel}", methods={"POST"}, requirements={"boek": "\d+", "profiel": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderbeschrijving(Boek $boek, Profiel $profiel) {
		$recensie = $this->boekRecensieRepository->get($boek, $profiel);
		if (!$recensie->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
			throw $this->createAccessDeniedException();
		} else {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($recensie);
			$manager->flush();
			setMelding('Recensie met succes verwijderd.', 1);

		}
		exit;
	}

	/**
	 * Verwijder boek
	 *
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderboek/{boek}", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderboek(Boek $boek) {
		if (!$boek->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving', -1);
			return $this->redirectToRoute('csrdelft_bibliotheek_catalogustonen');
		} else {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($boek);
			$manager->flush();
			setMelding('Boek met succes verwijderd.', 1);
			return $this->redirectToRoute('csrdelft_bibliotheek_catalogustonen');
		}
	}

	/**
	 * @param Request $request
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaar/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaar(Request $request, BoekExemplaar $exemplaar) {
		if (!$exemplaar->magBewerken()) {
			throw $this->createAccessDeniedException("Mag exemplaar niet bewerken");
		}
		$form = $this->createFormulier(BoekExemplaarFormulier::class, $exemplaar);
		$form->handleRequest($request);
		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($exemplaar);
			$manager->flush();
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]);
	}

	/**
	 * Exemplaar toevoegen
	 * @param Boek $boek
	 * @param Profiel|null $profiel
	 * @return RedirectResponse
	 * @Route("/bibliotheek/addexemplaar/{exemplaar}/{profiel}", methods={"POST"}, defaults={"profiel": null}, requirements={"exemplaar": "\d+", "profiel": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function addexemplaar(Boek $boek, Profiel $profiel = null) {
		if (!$boek->magBekijken()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()', -1);
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $boek->id]);
		}
		if ($profiel == null) {
			$profiel = $this->getProfiel();
		}
		if ($profiel->uid != $this->getUid() && !($profiel->uid == 'x222' && LoginService::mag(P_BIEB_MOD))) {
			throw $this->createAccessDeniedException('Mag deze eigenaar niet kiezen');
		}
		$this->boekExemplaarRepository->addExemplaar($boek, $profiel);

		setMelding('Exemplaar met succes toegevoegd.', 1);
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $boek->id]);
	}

	/**
	 * Exemplaar verwijderen
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderexemplaar/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderexemplaar(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($exemplaar);
			$manager->flush();
			setMelding('Exemplaar met succes verwijderd.', 1);
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarvermist/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarvermist(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setVermist($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als vermist.', 1);
			} else {
				setMelding('Exemplaar markeren als vermist mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * @param BoekExemplaar $exemplaar
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaargevonden/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaargevonden(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setGevonden($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als gevonden.', 1);
			} else {
				setMelding('Exemplaar markeren als gevonden mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return new JsonResponse($this->generateUrl('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]));
	}

	/**
	 * /exemplaaruitlenen/[exemplaarid]
	 * @param Request $request
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * TODO Deze methode wordt niet gebruikt, waarom?
	 */
	public function exemplaaruitlenen(Request $request, BoekExemplaar $exemplaar) {
		$uid = $request->request->get('lener_uid');
		if (!$exemplaar->isEigenaar()) {
			setMelding('Alleen de eigenaar mag boeken uitlenen', -1);
		} else if (!ProfielRepository::existsUid($uid)) {
			setMelding('Incorrecte lener', -1);
		} else if ($this->boekExemplaarRepository->leen($exemplaar, $uid)) {
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id, '_fragment' => 'exemplaren']);
		} else {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}

		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]);
	}


	/**
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarlenen/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarlenen(BoekExemplaar $exemplaar) {
		if (!$this->boekExemplaarRepository->leen($exemplaar, $this->getUid())) {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id, '_fragment' => 'exemplaren']);
	}


	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 *
	 * @param BoekExemplaar $exemplaar
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaarteruggegeven/{id}", methods={"POST"}, requirements={"id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarteruggegeven(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend() && $exemplaar->uitgeleend_uid == $this->getUid()) {
			if ($this->boekExemplaarRepository->terugGegeven($exemplaar)) {
				setMelding('Exemplaar is teruggegeven.', 1);
			} else {
				setMelding('Teruggave van exemplaar melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. ', -1);
		}
		return new JsonResponse($this->generateUrl('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]));
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 *
	 * @param BoekExemplaar $exemplaar
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaarterugontvangen/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarterugontvangen(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar() && ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven())) {
			if ($this->boekExemplaarRepository->terugOntvangen($exemplaar)) {
				setMelding('Exemplaar terugontvangen.', 1);
			} else {
				setMelding('Exemplaar terugontvangen melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()', -1);
		}
		return new JsonResponse($this->generateUrl('csrdelft_bibliotheek_boek', ['boek' => $exemplaar->boek->id]));
	}


	/**
	 * Genereert suggesties voor jquery-autocomplete
	 *
	 * /autocomplete/auteur
	 * @param Request $request
	 * @param $zoekveld
	 * @return JsonResponse
	 * @Route("/bibliotheek/autocomplete/{zoekveld}", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function autocomplete(Request $request, $zoekveld) {
		if ($request->query->has('q')) {
			$zoekterm = $request->query->get('q');

			$results = $this->boekRepository->autocompleteProperty($zoekveld, $zoekterm);
			$data = [];
			foreach ($results as $result) {
				$waarde = $result[$zoekveld];
				$data[] = ['data' => $waarde, 'value' => $waarde, 'id' => $waarde];
			}
			return new JsonResponse($data);
		} else {
			throw $this->createAccessDeniedException();
		}
	}

	/**
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Route("/bibliotheek/zoeken", methods={"POST"})
	 * @Auth(P_BIEB_READ)
	 */
	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = array();
		foreach ($this->boekRepository->autocompleteBoek($zoekterm) as $boek) {
			$result[] = array(
				'url' => $this->generateUrl('csrdelft_bibliotheek_boek', ['boek' => $boek->id]),
				'icon' => Icon::getTag('boek'),
				'label' => $boek->auteur,
				'value' => $boek->titel
			);
		}
		return new JsonResponse($result);
	}


}
