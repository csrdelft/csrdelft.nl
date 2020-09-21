<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\LDAP;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\LogRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\SavedQueryRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\Roodschopper;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Icon;
use CsrDelft\view\PlainView;
use CsrDelft\view\roodschopper\RoodschopperForm;
use CsrDelft\view\SavedQueryContent;
use CsrDelft\view\Streeplijstcontent;
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
class ToolsController extends AbstractController {
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var LogRepository
	 */
	private $logRepository;
	/**
	 * @var SavedQueryRepository
	 */
	private $savedQueryRepository;
	/**
	 * @var ProfielService
	 */
	private $profielService;
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;

	public function __construct(AccountRepository $accountRepository, ProfielRepository $profielRepository, ProfielService $profielService, SuService $suService, LogRepository $logRepository, SavedQueryRepository $savedQueryRepository, VerticalenRepository $verticalenRepository) {
		$this->savedQueryRepository = $savedQueryRepository;
		$this->accountRepository = $accountRepository;
		$this->profielRepository = $profielRepository;
		$this->suService = $suService;
		$this->logRepository = $logRepository;
		$this->profielService = $profielService;
		$this->verticalenRepository = $verticalenRepository;
	}

	/**
	 * @return PlainView|Response
	 * @Route("/tools/streeplijst", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function streeplijst() {
		$body = new Streeplijstcontent();

		# yuck
		if (isset($_GET['iframe'])) {
			return new PlainView($body->getHtml());
		} else {
			return $this->render('default.html.twig', ['content' => $body]);
		}
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/tools/stats", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function stats(Request $request) {
		if ($request->query->has('uid')) {
			$by = ['uid' => $request->query->get('uid')];
		} elseif ($request->query->has('ip')) {
			$by = ['ip' => $request->query->get('ip')];
		} else {
			$by = [];
		}

		$log = $this->logRepository->findBy($by, ['ID' => 'desc'], 30);

		return $this->render('stats/stats.html.twig', ['log' => $log]);
	}

	/**
	 * @return Response
	 * @Route("/tools/verticalelijsten", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function verticalelijsten() {
		return $this->render('tools/verticalelijst.html.twig', [
			'verticalen' => array_reduce(
				$this->verticalenRepository->findAll(),
				function ($carry, $verticale) {
					$carry[$verticale->naam] = $this->profielRepository->createQueryBuilder('p')
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
	public function roodschopper(Request $request) {
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
			return $this->csrRedirect('/tools/roodschopper?verzenden=true&aantal=' . count($roodschopper->getSaldi()));
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
	 * @return PlainView
	 * @Route("/tools/syncldap", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function syncldap() {
		if (DEBUG || LoginService::mag(P_ADMIN) || $this->suService->isSued()) {
			$ldap = new LDAP();
			foreach ($this->profielRepository->findAll() as $profiel) {
				$this->profielRepository->save_ldap($profiel, $ldap);
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
	public function phpinfo() {
		ob_start();
		phpinfo();
		return new PlainView(ob_get_clean());
	}

	/**
	 * @return Response
	 * @Route("/tools/admins", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function admins() {
		return $this->render('tools/admins.html.twig', [
			'accounts' => $this->accountRepository->findAdmins(),
		]);
	}

	/**
	 * Voor de NovCie, zorgt ervoor dat novieten bekeken kunnen worden als dat afgeschermd is op de rest van de stek.
	 *
	 * @return Response
	 * @Route("/tools/novieten", methods={"GET"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novieten() {
		return $this->render('tools/novieten.html.twig', [
			'novieten' => $this->profielRepository->findBy(['status' => LidStatus::Noviet, 'lidjaar' => date('Y')])
		]);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/tools/dragobject", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function dragobject(Request $request) {
		$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
		$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

		$request->getSession()->set("dragobject_$id", $coords);

		return new JsonResponse(null);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/naamlink", methods={"GET", "POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamlink() {
//is er een uid gegeven?
		$given = 'uid';
		if (isset($_GET['uid'])) {
			$string = urldecode($_GET['uid']);
		} elseif (isset($_POST['uid'])) {
			$string = $_POST['uid'];

//is er een naam gegeven?
		} elseif (isset($_GET['naam'])) {
			$string = urldecode($_GET['naam']);
			$given = 'naam';
		} elseif (isset($_POST['naam'])) {
			$string = $_POST['naam'];
			$given = 'naam';
		} else { //geen input
			throw new CsrGebruikerException('Geen naam invoer in naamlink');
		}

//welke subset van leden?
		$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$toegestanezoekfilters = ['leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'];
		if (isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}

		function uid2naam($uid) {
			$naam = ProfielRepository::getLink($uid, 'civitas');
			if ($naam) {
				return $naam;
			} else {
				return 'Lid[' . htmlspecialchars($uid) . '] &notin; db.';
			}
		}

		if ($given == 'uid') {
			if ($this->accountRepository->isValidUid($string)) {
				return new PlainView(uid2naam($string));
			} else {
				$uids = explode(',', $string);
				foreach ($uids as $uid) {
					return new PlainView(uid2naam($uid));
				}
			}
		} elseif ($given == 'naam') {
			$namen = $this->profielService->zoekLeden($string, 'naam', 'alle', 'achternaam', $zoekin);
			if (!empty($namen)) {
				if (count($namen) === 1) {
					return new PlainView($namen[0]->getLink('civitas'));
				} else {
					return new PlainView('Meerdere leden mogelijk');
				}
			}
			return new PlainView('Geen lid gevonden');
		}

		throw new NotFoundHttpException();
	}

	/**
	 * @param null $zoekin
	 * @param string $query
	 * @return JsonResponse
	 * @Route("/tools/naamsuggesties", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamsuggesties($zoekin = null, $query = '') {
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

		$profielen = $this->profielService->zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $limiet);

		$scoredProfielen = [];
		foreach ($profielen as $profiel) {
			$score = 0;

			// Beste match start met de zoekterm
			if (startsWith(strtolower($profiel->getNaam('volledig')), strtolower($query))) {
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
				'icon' => Icon::getTag('profiel', null, 'Profiel', 'mr-2'),
				'url' => '/profiel/' . $profiel->uid,
				'label' => $profiel->uid,
				'value' => $profiel->getNaam($vorm),
				'uid' => $profiel->uid,
			);
		}

		return new JsonResponse($result);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/memcachestats", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function memcachestats() {
		if (DEBUG || LoginService::mag(P_ADMIN) || $this->suService->isSued()) {
			ob_start();

			echo getMelding();
			echo '<h1>MemCache statistieken</h1>';
			debugprint($this->get('stek.cache.memcache')->getStats());

			return new PlainView(ob_get_clean());
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/tools/query", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function query(Request $request) {
		if ($request->query->has('id')) {
			$id = $request->query->getInt('id');
			$result = $this->savedQueryRepository->loadQuery($id);
		} else {
			$result = null;
		}

		return $this->render('default.html.twig', [
			'content' => new SavedQueryContent($result),
		]);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/bbcode", methods={"GET", "POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function bbcode() {
		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);

		if (isset($_POST['data'])) {
			$string = urldecode($_POST['data']);
		} elseif (isset($_GET['data'])) {
			$string = $_GET['data'];
		} elseif (isset($input['data'])) {
			$string = urldecode($input['data']);
		} else {
			$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/bbcode';
		}

		$string = trim($string);

		if (isset($_POST['mail']) || isset($input['mail'])) {
			return new PlainView(CsrBB::parseMail($string));
		} else {
			return new PlainView(CsrBB::parse($string));
		}
	}
}
