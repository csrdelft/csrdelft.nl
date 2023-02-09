<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\entity\bibliotheek\BiebAuteur;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\bibliotheek\BiebAuteurRepository;
use CsrDelft\repository\bibliotheek\BiebRubriekRepository;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use CsrDelft\repository\bibliotheek\BoekRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\BoekImporter;
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
class BibliotheekController extends AbstractController
{
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
	/**
	 * @var BiebAuteurRepository
	 */
	private $biebAuteurRepository;

	public function __construct(
		BoekExemplaarRepository $boekExemplaarRepository,
		BoekRepository $boekRepository,
		BoekRecensieRepository $boekRecensieRepository,
		BiebRubriekRepository $biebRubriekRepository,
		BiebAuteurRepository $biebAuteurRepository,
		CmsPaginaRepository $cmsPaginaRepository
	) {
		$this->boekExemplaarRepository = $boekExemplaarRepository;
		$this->boekRepository = $boekRepository;
		$this->boekRecensieRepository = $boekRecensieRepository;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
		$this->biebRubriekRepository = $biebRubriekRepository;
		$this->biebAuteurRepository = $biebAuteurRepository;
	}

	/**
	 * @param Request $request
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/boek/{boek}/recensie", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function recensie(Request $request, Boek $boek): RedirectResponse
	{
		$recensie = $this->boekRecensieRepository->get($boek, $this->getProfiel());
		$formulier = $this->createFormulier(
			BoekRecensieFormulier::class,
			$recensie
		);
		$formulier->handleRequest($request);
		if ($formulier->validate()) {
			if (!$recensie->magBewerken()) {
				throw $this->createAccessDeniedException('Mag recensie niet bewerken');
			} else {
				$recensie->bewerkdatum = date_create_immutable();
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($recensie);
				$manager->flush();
				$this->addFlash(FlashType::INFO, 'Recensie opgeslagen');
			}
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $boek->id,
		]);
	}

	/**
	 * @return Response
	 * @Route("/bibliotheek/rubrieken", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function rubrieken(): Response
	{
		return $this->render('default.html.twig', [
			'content' => new CmsPaginaView(
				$this->cmsPaginaRepository->find('rubrieken')
			),
		]);
	}

	/**
	 * @return Response
	 * @Route("/bibliotheek/wenslijst", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function wenslijst(): Response
	{
		return $this->render('default.html.twig', [
			'content' => new CmsPaginaView(
				$this->cmsPaginaRepository->find('wenslijst')
			),
		]);
	}

	/**
	 * @Route("/bibliotheek", methods={"GET"})
	 * @Auth(P_BIEB_READ)
	 */
	public function catalogustonen(): Response
	{
		return $this->render('default.html.twig', [
			'content' => new BibliotheekCatalogusDatatable(),
		]);
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 * @Route("/bibliotheek/catalogusdata", methods={"POST"})
	 * @Auth(P_BIEB_READ)
	 * @param Request $request
	 * @return BibliotheekCatalogusDatatableResponse
	 */
	public function catalogusdata(
		Request $request
	): BibliotheekCatalogusDatatableResponse {
		$boeken = $this->boekRepository->findAll();
		$uid = $request->query->get('eigenaar');
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
	public function boek(Request $request, Boek $boek = null): Response
	{
		if ($boek == null) {
			$boek = new Boek();
		}
		$boekForm = $this->createFormulier(BoekFormulier::class, $boek);
		$boekForm->handleRequest($request);
		if ($boekForm->validate()) {
			if (!$boek->magBewerken()) {
				throw $this->createAccessDeniedException(
					'U mag dit boek niet bewerken'
				);
			} else {
				$auteur = $this->biebAuteurRepository->findOneBy([
					'auteur' => $boek->auteur,
				]);

				if (!$auteur) {
					$auteur = new BiebAuteur();
					$auteur->auteur = $boek->auteur;
					$this->getDoctrine()
						->getManager()
						->persist($auteur);
				}

				$boek->auteur2 = $auteur;

				$boek->setCategorie(
					$this->biebRubriekRepository->find($boek->categorie_id)
				);
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($boek);
				$manager->flush();

				return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
					'boek' => $boek->id,
				]);
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
				$exemplaarFormulieren[$exemplaar->id] = $this->createFormulier(
					BoekExemplaarFormulier::class,
					$exemplaar
				)->createView();
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
			'recensieFormulier' => $boek->id
				? $this->createFormulier(
					BoekRecensieFormulier::class,
					$mijnRecensie
				)->createView()
				: null,
			'exemplaarFormulieren' => $exemplaarFormulieren,
		]);
	}

	/**
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/import/{boek}", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function import(Boek $boek): RedirectResponse
	{
		if (!$boek->isEigenaar()) {
			throw $this->createAccessDeniedException();
		} else {
			$importer = new BoekImporter();
			$importer->import($boek);
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($boek);
			$manager->flush();
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
				'boek' => $boek->id,
			]);
		}
	}

	/**
	 * @param Boek $boek
	 * @param Profiel $profiel
	 * @Route("/bibliotheek/verwijderbeschrijving/{boek}/{profiel}", methods={"POST"}, requirements={"boek": "\d+", "profiel": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderbeschrijving(Boek $boek, Profiel $profiel)
	{
		$recensie = $this->boekRecensieRepository->get($boek, $profiel);
		if (!$recensie->magVerwijderen()) {
			$this->addFlash(FlashType::ERROR, 'Onvoldoende rechten voor deze actie.');
			throw $this->createAccessDeniedException();
		} else {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($recensie);
			$manager->flush();
			$this->addFlash(FlashType::SUCCESS, 'Recensie met succes verwijderd.');
		}
		exit();
	}

	/**
	 * Verwijder boek
	 *
	 * @param Boek $boek
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderboek/{boek}", methods={"POST"}, requirements={"boek": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderboek(Boek $boek): RedirectResponse
	{
		if (!$boek->magVerwijderen()) {
			$this->addFlash(
				FlashType::ERROR,
				'Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving'
			);
			return $this->redirectToRoute('csrdelft_bibliotheek_catalogustonen');
		} else {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($boek);
			$manager->flush();
			$this->addFlash(FlashType::SUCCESS, 'Boek met succes verwijderd.');
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
	public function exemplaar(
		Request $request,
		BoekExemplaar $exemplaar
	): RedirectResponse {
		if (!$exemplaar->magBewerken()) {
			throw $this->createAccessDeniedException('Mag exemplaar niet bewerken');
		}
		$form = $this->createFormulier(BoekExemplaarFormulier::class, $exemplaar);
		$form->handleRequest($request);
		if ($form->validate()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($exemplaar);
			$manager->flush();
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $exemplaar->boek->id,
		]);
	}

	/**
	 * Exemplaar toevoegen
	 * @param Boek $boek
	 * @param Profiel|null $profiel
	 * @return RedirectResponse
	 * @Route("/bibliotheek/addexemplaar/{boek}/{profiel}", methods={"POST"}, defaults={"profiel": null}, requirements={"boek": "\d+", "profiel": ".{4}"})
	 * @Auth(P_BIEB_READ)
	 */
	public function addexemplaar(
		Boek $boek,
		Profiel $profiel = null
	): RedirectResponse {
		if (!$boek->magBekijken()) {
			$this->addFlash(
				FlashType::ERROR,
				'Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()'
			);
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
				'boek' => $boek->id,
			]);
		}
		if ($profiel == null) {
			$profiel = $this->getProfiel();
		}
		if (
			$profiel->uid != $this->getUid() &&
			!($profiel->uid == 'x222' && $this->mag(P_BIEB_MOD))
		) {
			throw $this->createAccessDeniedException('Mag deze eigenaar niet kiezen');
		}
		$this->boekExemplaarRepository->addExemplaar($boek, $profiel);

		$this->addFlash(FlashType::SUCCESS, 'Exemplaar met succes toegevoegd.');
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $boek->id,
		]);
	}

	/**
	 * Exemplaar verwijderen
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/verwijderexemplaar/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function verwijderexemplaar(BoekExemplaar $exemplaar): RedirectResponse
	{
		if ($exemplaar->isEigenaar()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($exemplaar);
			$manager->flush();
			$this->addFlash(FlashType::SUCCESS, 'Exemplaar met succes verwijderd.');
		} else {
			$this->addFlash(FlashType::ERROR, 'Onvoldoende rechten voor deze actie.');
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $exemplaar->boek->id,
		]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarvermist/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarvermist(BoekExemplaar $exemplaar): RedirectResponse
	{
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setVermist($exemplaar)) {
				$this->addFlash(
					FlashType::SUCCESS,
					'Exemplaar gemarkeerd als vermist.'
				);
			} else {
				$this->addFlash(
					FlashType::ERROR,
					'Exemplaar markeren als vermist mislukt. '
				);
			}
		} else {
			$this->addFlash(FlashType::ERROR, 'Onvoldoende rechten voor deze actie.');
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $exemplaar->boek->id,
		]);
	}

	/**
	 * Exemplaar als vermist markeren
	 * @param BoekExemplaar $exemplaar
	 * @return JsonResponse
	 * @Route("/bibliotheek/exemplaargevonden/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaargevonden(BoekExemplaar $exemplaar): JsonResponse
	{
		if ($exemplaar->isEigenaar()) {
			if ($this->boekExemplaarRepository->setGevonden($exemplaar)) {
				$this->addFlash(
					FlashType::SUCCESS,
					'Exemplaar gemarkeerd als gevonden.'
				);
			} else {
				$this->addFlash(
					FlashType::ERROR,
					'Exemplaar markeren als gevonden mislukt. '
				);
			}
		} else {
			$this->addFlash(FlashType::ERROR, 'Onvoldoende rechten voor deze actie.');
		}
		return new JsonResponse(
			$this->generateUrl('csrdelft_bibliotheek_boek', [
				'boek' => $exemplaar->boek->id,
			])
		);
	}

	/**
	 * /exemplaaruitlenen/[exemplaarid]
	 * @param Request $request
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * TODO Deze methode wordt niet gebruikt, waarom?
	 */
	public function exemplaaruitlenen(
		Request $request,
		BoekExemplaar $exemplaar
	): RedirectResponse {
		$uid = $request->request->get('lener_uid');
		if (!$exemplaar->isEigenaar()) {
			$this->addFlash(
				FlashType::ERROR,
				'Alleen de eigenaar mag boeken uitlenen'
			);
		} elseif (!ProfielRepository::existsUid($uid)) {
			$this->addFlash(FlashType::ERROR, 'Incorrecte lener');
		} elseif ($this->boekExemplaarRepository->leen($exemplaar, $uid)) {
			return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
				'boek' => $exemplaar->boek->id,
				'_fragment' => 'exemplaren',
			]);
		} else {
			$this->addFlash(FlashType::ERROR, 'Kan dit exemplaar niet lenen');
		}

		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $exemplaar->boek->id,
		]);
	}

	/**
	 * @param BoekExemplaar $exemplaar
	 * @return RedirectResponse
	 * @Route("/bibliotheek/exemplaarlenen/{exemplaar}", methods={"POST"}, requirements={"exemplaar": "\d+"})
	 * @Auth(P_BIEB_READ)
	 */
	public function exemplaarlenen(BoekExemplaar $exemplaar): RedirectResponse
	{
		if (!$this->boekExemplaarRepository->leen($exemplaar, $this->getUid())) {
			$this->addFlash(FlashType::ERROR, 'Kan dit exemplaar niet lenen');
		}
		return $this->redirectToRoute('csrdelft_bibliotheek_boek', [
			'boek' => $exemplaar->boek->id,
			'_fragment' => 'exemplaren',
		]);
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
	public function exemplaarteruggegeven(BoekExemplaar $exemplaar): JsonResponse
	{
		if (
			$exemplaar->isUitgeleend() &&
			$exemplaar->uitgeleend_uid == $this->getUid()
		) {
			if ($this->boekExemplaarRepository->terugGegeven($exemplaar)) {
				$this->addFlash(FlashType::SUCCESS, 'Exemplaar is teruggegeven.');
			} else {
				$this->addFlash(
					FlashType::ERROR,
					'Teruggave van exemplaar melden is mislukt. '
				);
			}
		} else {
			$this->addFlash(
				FlashType::ERROR,
				'Onvoldoende rechten voor deze actie. '
			);
		}
		return new JsonResponse(
			$this->generateUrl('csrdelft_bibliotheek_boek', [
				'boek' => $exemplaar->boek->id,
			])
		);
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
	public function exemplaarterugontvangen(
		BoekExemplaar $exemplaar
	): JsonResponse {
		if (
			$exemplaar->isEigenaar() &&
			($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven())
		) {
			if ($this->boekExemplaarRepository->terugOntvangen($exemplaar)) {
				$this->addFlash(FlashType::SUCCESS, 'Exemplaar terugontvangen.');
			} else {
				$this->addFlash(
					FlashType::ERROR,
					'Exemplaar terugontvangen melden is mislukt. '
				);
			}
		} else {
			$this->addFlash(
				FlashType::ERROR,
				'Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()'
			);
		}
		return new JsonResponse(
			$this->generateUrl('csrdelft_bibliotheek_boek', [
				'boek' => $exemplaar->boek->id,
			])
		);
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
	public function autocomplete(Request $request, $zoekveld): JsonResponse
	{
		if ($request->query->has('q')) {
			$zoekterm = $request->query->get('q');

			$results = $this->boekRepository->autocompleteProperty(
				$zoekveld,
				$zoekterm
			);
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
	public function zoeken(Request $request, $zoekterm = null): JsonResponse
	{
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}
		$result = [];
		foreach ($this->boekRepository->autocompleteBoek($zoekterm) as $boek) {
			$result[] = [
				'url' => $this->generateUrl('csrdelft_bibliotheek_boek', [
					'boek' => $boek->id,
				]),
				'icon' => Icon::getTag('boek'),
				'label' => $boek->auteur,
				'value' => $boek->titel,
			];
		}
		return new JsonResponse($result);
	}
}
