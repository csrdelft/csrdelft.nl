<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekRecensie;
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
use CsrDelft\view\bibliotheek\RecensieFormulier;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
	 * @param $boek_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/boek/{boek_id}/recensie", methods={"POST"}, requirements={"boek_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function recensie($boek_id) {
		$recensie = $this->boekRecensieRepository->get($boek_id, LoginService::getUid());
		$formulier = new RecensieFormulier($recensie);
		if ($formulier->validate()) {
			if (!$recensie->magBewerken()) {
				throw new CsrToegangException("Mag recensie niet bewerken");
			} else {
				$recensie->bewerkdatum = getDateTime();
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($recensie);
				$manager->flush();
				setMelding("Recensie opgeslagen", 0);
			}
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $boek_id]);
	}

	/**
	 * @return TemplateView
	 * @Route("/bibliotheek/rubrieken", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function rubrieken() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('rubrieken'))]);
	}

	/**
	 * @return TemplateView
	 * @Route("/bibliotheek/wenslijst", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function wenslijst() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('wenslijst'))]);
	}

	/**
	 * @Route("/bibliotheek", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function catalogustonen() {
		return view('default', ['content' => new BibliotheekCatalogusDatatable()]);
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 * @Route("/bibliotheek/catalogusdata", methods={"POST"})
	 * @Auth(P_BIEB_READ)
	 */
	public function catalogusdata() {
		$data = $this->boekRepository->findAll();
		$uid = filter_input(INPUT_GET, "eigenaar", FILTER_SANITIZE_STRING);
		$results = [];
		if ($uid !== null) {
			foreach ($data as $boek) {
				if ($boek->isEigenaar($uid)) {
					$results[] = $boek;
				}
			}
		} else {
			$results = $data;
		}
		return new BibliotheekCatalogusDatatableResponse($results);
	}

	/**
	 * Boek weergeven
	 * @param $boek_id
	 * @return TemplateView|RedirectResponse
	 * @Route("/bibliotheek/boek/{boek_id}", methods={"GET", "POST"}, defaults={"boek_id": null}, requirements={"boek_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function boek($boek_id = null) {
		if ($boek_id == null) {
			$boek = new Boek();
		} else {
			$boek = $this->boekRepository->find($boek_id);
		}
		$boekForm = new BoekFormulier($boek);

		if ($boekForm->validate()) {
			if (!$boek->magBewerken()) {
				throw new CsrToegangException('U mag dit boek niet bewerken');
			} else {
				$boek->setCategorie($this->biebRubriekRepository->find($boek->categorie_id));
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($boek);
				$manager->flush();

				return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $boek->id]);
			}
		}

		$alleRecensies = $boek->getRecensies();
		$andereRecensies = [];
		$mijnRecensie = new BoekRecensie();
		$mijnRecensie->boek_id = $boek->id;
		$exemplaarFormulieren = [];
		foreach ($boek->getExemplaren() as $exemplaar) {
			if ($exemplaar->magBewerken()) {
				$exemplaarFormulieren[$exemplaar->id] = new BoekExemplaarFormulier($exemplaar);
			}
		}
		foreach ($alleRecensies as $recensie) {
			if ($recensie->schrijver_uid == LoginService::getUid()) {
				$mijnRecensie = $recensie;
			}
			$andereRecensies[] = $recensie;

		}
		$recensieForm = new RecensieFormulier($mijnRecensie);
		return view('bibliotheek.boek', [
			'boek' => $boek,
			'recensies' => $andereRecensies,
			'boekFormulier' => $boekForm,
			'recensieFormulier' => $recensieForm,
			'exemplaarFormulieren' => $exemplaarFormulieren,
		]);
	}

	/**
	 * @param $boek_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/import/{boek_id}", methods={"POST"}, requirements={"boek_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function import($boek_id) {
		$boek = $this->boekRepository->find($boek_id);
		if (!$boek->isEigenaar()) {
			throw new CsrToegangException();
		} else {
			$importer = new BoekImporter();
			$importer->import($boek);
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($boek);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $boek->id]);
		}
	}

	/**
	 * @param $boek_id
	 * @param $uid
	 * @Route("/bibliotheek/verwijderbeschrijving/{boek_id}/{uid}", methods={"POST"}, requirements={"boek_id": "\d+", "uid": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderbeschrijving($boek_id, $uid) {
		$recensie = $this->boekRecensieRepository->get($boek_id, $uid);
		if (!$recensie->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
			throw new CsrToegangException();
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
	 * @param $boek_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderboek/{boek_id}", methods={"POST"}, requirements={"boek_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderboek($boek_id) {
		$boek = $this->boekRepository->find($boek_id);

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
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaar/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaar($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if (!$exemplaar->magBewerken()) {
			throw new CsrToegangException("Mag exemplaar niet bewerken");
		}
		$form = new BoekExemplaarFormulier($exemplaar);
		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($exemplaar);
			$manager->flush();
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar toevoegen
	 * @param string $boek_id
	 * @param string|null $uid
	 * @return RedirectResponse
	 * @Route("/bibliotheek/addexemplaar/{boek_id}/{uid}", methods={"POST"}, defaults={"uid": null}, requirements={"boek_id": "\d+", "uid": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function addexemplaar($boek_id, $uid = null) {
		$boek = $this->boekRepository->find($boek_id);
		if (!$boek->magBekijken()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()', -1);
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $boek->id]);
		}
		if ($uid == null) {
			$uid = LoginService::getUid();
		}
		if ($uid != LoginService::getUid() && !($uid == 'x222' && LoginService::mag(P_BIEB_MOD))) {
			throw new CsrToegangException('Mag deze eigenaar niet kiezen');
		}
		$this->boekExemplaarRepository->addExemplaar($boek, $uid);

		setMelding('Exemplaar met succes toegevoegd.', 1);
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $boek->id]);
	}

	/**
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$exemplaarid
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderexemplaar/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderexemplaar($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if ($exemplaar->isEigenaar()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($exemplaar);
			$manager->flush();
			setMelding('Exemplaar met succes verwijderd.', 1);
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaarvermist/[id]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarvermist/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarvermist($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);

		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setVermist($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als vermist.', 1);
			} else {
				setMelding('Exemplaar markeren als vermist mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaargevonden/[id]
	 * @param $exemplaar_id
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaargevonden/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaargevonden($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setGevonden($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als gevonden.', 1);
			} else {
				setMelding('Exemplaar markeren als gevonden mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
	}

	/**
	 * /exemplaaruitlenen/[exemplaarid]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 * TODO Deze methode wordt niet gebruikt, waarom?
	 */
	public function exemplaaruitlenen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		$uid = filter_input(INPUT_POST, 'lener_uid', FILTER_SANITIZE_STRING);
		if (!$exemplaar->isEigenaar()) {
			setMelding('Alleen de eigenaar mag boeken uitlenen', -1);
		} else if (!ProfielRepository::existsUid($uid)) {
			setMelding('Incorrecte lener', -1);
		} else if ($this->boekExemplaarRepository->leen($exemplaar, $uid)) {
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id, '_fragment' => 'exemplaren']);
		} else {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}

		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id]);
	}


	/**
	 * /exemplaarlenen/[exemplaarid]
	 * @param $exemplaar_id
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarlenen/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarlenen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if (!$this->boekExemplaarRepository->leen($exemplaar, LoginService::getUid())) {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', ['boek_id' => $exemplaar->getBoek()->id, '_fragment' => 'exemplaren']);
	}


	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 *
	 * /exemplaarteruggegeven/[exemplaarid]
	 * @param $exemplaar_id
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaarteruggegeven/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarteruggegeven($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if ($exemplaar->isUitgeleend() && $exemplaar->uitgeleend_uid == LoginService::getUid()) {
			if ($this->boekExemplaarRepository->terugGegeven($exemplaar)) {
				setMelding('Exemplaar is teruggegeven.', 1);
			} else {
				setMelding('Teruggave van exemplaar melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. ', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->boek->id);
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 *
	 * /exemplaarterugontvangen/exemplaarid
	 * @param $exemplaar_id
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaarterugontvangen/{exemplaar_id}", methods={"POST"}, requirements={"exemplaar_id": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarterugontvangen($exemplaar_id) {
		$exemplaar = $this->boekExemplaarRepository->get($exemplaar_id);
		if ($exemplaar->isEigenaar() && ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven())) {
			if ($this->boekExemplaarRepository->terugOntvangen($exemplaar)) {
				setMelding('Exemplaar terugontvangen.', 1);
			} else {
				setMelding('Exemplaar terugontvangen melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()', -1);
		}
		return new JsonResponse('/bibliotheek/boek/' . $exemplaar->boek->id);
	}


	/**
	 * Genereert suggesties voor jquery-autocomplete
	 *
	 * /autocomplete/auteur
	 * @param $zoekveld
	 * @return JsonResponse
	 * @Route("/bibliotheek/autocomplete/{zoekveld}", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function autocomplete($zoekveld) {
		if (isset($_GET['q'])) {
			$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

			$results = $this->boekRepository->autocompleteProperty($zoekveld, $zoekterm);
			$data = [];
			foreach ($results as $result) {
				$data[] = ['data' => [$result], 'value' => $result->{$zoekveld}, 'id' => $result->id];
			}
			return new JsonResponse($data);
		} else {
			throw new CsrToegangException();
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
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = array();
		foreach ($this->boekRepository->autocompleteBoek($zoekterm) as $boek) {
			$result[] = array(
				'url' => '/bibliotheek/boek/' . $boek->id,
				'icon' => Icon::getTag('boek'),
				'label' => $boek->auteur,
				'value' => $boek->titel
			);
		}
		return new JsonResponse($result);
	}


}
