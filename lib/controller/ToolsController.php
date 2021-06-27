<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\LDAP;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\SavedQueryRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\Roodschopper;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\Icon;
use CsrDelft\view\PlainView;
use CsrDelft\view\roodschopper\RoodschopperForm;
use CsrDelft\view\SavedQueryContent;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deze controller bevat een aantal beheertools die niet direct onder een andere controller geschaard kunnen worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class ToolsController extends AbstractController
{
	/**
	 * @param VerticalenRepository $verticalenRepository
	 * @param ProfielRepository $profielRepository
	 * @return Response
	 * @Route("/tools/verticalelijsten", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function verticalelijsten(VerticalenRepository $verticalenRepository, ProfielRepository $profielRepository): Response
	{
		return $this->render('tools/verticalelijst.html.twig', [
			'verticalen' => array_reduce(
				$verticalenRepository->findAll(),
				function ($carry, $verticale) use ($profielRepository) {
					$carry[$verticale->naam] = $profielRepository->createQueryBuilder('p')
						->where('p.verticale = :verticale and p.status in (:lidstatus)')
						->setParameter('verticale', $verticale->letter)
						->setParameter('lidstatus', LidStatus::getFiscaalLidLike())
						->getQuery()->getResult();
					return $carry;
				},
				[]
			)
		]);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/tools/roodschopper", methods={"GET", "POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function roodschopper(Request $request): Response
	{
		if ($request->query->has('verzenden')) {
			return $this->render('tools/roodschopper.html.twig', [
				'verzenden' => true,
				'aantal' => $request->query->get('aantal'),
			]);
		}

		$roodschopper = Roodschopper::getDefaults();
		$roodschopperForm = new RoodschopperForm($roodschopper);

		if ($roodschopperForm->isPosted() && $roodschopperForm->validate() && $roodschopper->verzenden) {
			$roodschopper->sendMails();
			// Voorkom dubbele submit
			return $this->redirectToRoute(
				'csrdelft_tools_roodschopper',
				['verzenden' => true, 'aantal' => count($roodschopper->getSaldi())]
			);
		} else {
			$roodschopper->generateMails();
		}

		return $this->render('tools/roodschopper.html.twig', [
			'verzenden' => false,
			'form' => $roodschopperForm,
			'saldi' => $roodschopper->getSaldi(),
		]);
	}

	/**
	 * @param ProfielRepository $profielRepository
	 * @param SuService $suService
	 * @return PlainView
	 * @Route("/tools/syncldap", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function syncldap(ProfielRepository $profielRepository, SuService $suService): PlainView
	{
		if (DEBUG || LoginService::mag(P_ADMIN) || $suService->isSued()) {
			$ldap = new LDAP();
			foreach ($profielRepository->findAll() as $profiel) {
				$profielRepository->save_ldap($profiel, $ldap);
			}

			$ldap->disconnect();

			return new PlainView('done');
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @return PlainView
	 * @Route("/tools/phpinfo", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function phpinfo(): PlainView
	{
		ob_start();
		phpinfo();
		return new PlainView(ob_get_clean());
	}

	/**
	 * @param AccountRepository $accountRepository
	 * @return Response
	 * @Route("/tools/admins", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function admins(AccountRepository $accountRepository): Response
	{
		return $this->render('tools/admins.html.twig', [
			'accounts' => $accountRepository->findAdmins(),
		]);
	}

	/**
	 * Voor de NovCie, zorgt ervoor dat novieten bekeken kunnen worden als dat afgeschermd is op de rest van de stek.
	 *
	 * @param ProfielRepository $profielRepository
	 * @return Response
	 * @Route("/tools/novieten", methods={"GET"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novieten(ProfielRepository $profielRepository): Response
	{
		return $this->render('tools/novieten.html.twig', [
			'novieten' => $profielRepository->findBy(['status' => LidStatus::Noviet, 'lidjaar' => date('Y')])
		]);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/tools/dragobject", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function dragobject(Request $request): JsonResponse
	{
		$id = $request->request->get('id');
		$coords = $request->request->get('coords');

		$request->getSession()->set("dragobject_$id", $coords);

		return new JsonResponse(null);
	}

	/**
	 * @param Request $request
	 * @param AccountRepository $accountRepository
	 * @param ProfielService $profielService
	 * @return PlainView
	 * @Route("/tools/naamlink", methods={"GET", "POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamlink(Request $request, AccountRepository $accountRepository, ProfielService $profielService): PlainView
	{
		$uid = $request->get('uid');
		$naam = $request->get('naam');
		$zoekin = $request->query->get('zoekin');

		if ($uid) {
			$string = $uid;
			$given = 'uid';
		} elseif ($naam) {
			$string = $naam;
			$given = 'naam';
		} else {
			throw new CsrGebruikerException('Geen naam invoer in naamlink');
		}

//welke subset van leden?
		$toegestanezoekfilters = ['leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'];
		if (!$zoekin || !in_array($zoekin, $toegestanezoekfilters)) {
			$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		}

		function uid2naam($uid): string
		{
			$naam = ProfielRepository::getLink($uid);
			if ($naam) {
				return $naam;
			} else {
				return 'Lid[' . htmlspecialchars($uid) . '] &notin; db.';
			}
		}

		if ($given == 'uid') {
			if ($accountRepository->isValidUid($string)) {
				return new PlainView(uid2naam($string));
			} else {
				$uids = explode(',', $string);
				foreach ($uids as $uid) {
					return new PlainView(uid2naam($uid));
				}
			}
		} elseif ($given == 'naam') {
			$namen = $profielService->zoekLeden($string, 'naam', 'alle', 'achternaam', $zoekin);
			if (!empty($namen)) {
				if (count($namen) === 1) {
					return new PlainView($namen[0]->getLink());
				} else {
					return new PlainView('Meerdere leden mogelijk');
				}
			}
			return new PlainView('Geen lid gevonden');
		}

		throw new NotFoundHttpException();
	}

	/**
	 * @param ProfielService $profielService
	 * @param null $zoekin
	 * @param string $query
	 * @return JsonResponse
	 * @Route("/tools/naamsuggesties", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamsuggesties(ProfielService $profielService, $zoekin = null, $query = ''): JsonResponse
	{
		//welke subset van leden?
		if (empty($zoekin)) {
			$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		}
		$toegestanezoekfilters = array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies');
		if (empty($zoekin) && isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}
		if (empty($zoekin) && isset($_GET['zoekin']) && $_GET['zoekin'] === 'voorkeur') {
			$zoekin = lid_instelling('forum', 'lidSuggesties');
		}

		if (empty($query) && isset($_GET['q'])) {
			$query = $_GET['q'];
		}
		$limiet = 20;
		if (isset($_GET['limit'])) {
			$limiet = (int)$_GET['limit'];
		}

		$toegestaneNaamVormen = ['user', 'volledig', 'streeplijst', 'voorletters', 'bijnaam', 'Duckstad', 'civitas', 'aaidrom'];
		$vorm = 'volledig';
		if (isset($_GET['vorm']) && in_array($_GET['vorm'], $toegestaneNaamVormen)) {
			$vorm = $_GET['vorm'];
		}

		$profielen = $profielService->zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $limiet);

		$scoredProfielen = [];
		foreach ($profielen as $profiel) {
			$score = 0;

			// Beste match start met de zoekterm
			if (str_starts_with(strtolower($profiel->getNaam()), strtolower($query))) {
				$score += 100;
			}

			// Zoek meest lijkende match
			$score -= levenshtein($query, $profiel->getNaam());

			$scoredProfielen[] = [
				'profiel' => $profiel,
				'score' => $score,
			];
		}

		usort($scoredProfielen, function ($a, $b) {
			return $b['score'] - $a['score'];
		});

		$scoredProfielen = array_slice($scoredProfielen, 0, 5);

		$result = array();
		foreach ($scoredProfielen as $scoredProfiel) {
			/** @var Profiel $profiel */
			$profiel = $scoredProfiel['profiel'];

			$result[] = array(
				'icon' => Icon::getTag('profiel', null, 'Profiel', 'me-2'),
				'url' => '/profiel/' . $profiel->uid,
				'label' => $profiel->uid,
				'value' => $profiel->getNaam($vorm),
				'uid' => $profiel->uid,
			);
		}

		return new JsonResponse($result);
	}

	/**
	 * @param SuService $suService
	 * @return PlainView
	 * @Route("/tools/memcachestats", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function memcachestats(SuService $suService): PlainView
	{
		if (DEBUG || LoginService::mag(P_ADMIN) || $suService->isSued()) {
			ob_start();

			echo getMelding();
			echo '<h1>MemCache statistieken</h1>';
			try {
				$memcached = MemcachedAdapter::createConnection($this->getParameter('memcached_url'));

				debugprint(current($memcached->getStats()));
			} catch (ServiceNotFoundException $ex) {
				echo 'Memcache is niet ingesteld.';
			}

			return new PlainView(ob_get_clean());
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param Request $request
	 * @param SavedQueryRepository $savedQueryRepository
	 * @return Response
	 * @Route("/tools/query", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function query(Request $request, SavedQueryRepository $savedQueryRepository): Response
	{
		if ($request->query->has('id')) {
			$id = $request->query->getInt('id');
			$result = $savedQueryRepository->loadQuery($id);
		} else {
			$result = null;
		}

		return $this->render('default.html.twig', [
			'content' => new SavedQueryContent($result),
		]);
	}
}
