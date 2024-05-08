<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingController extends AbstractController
{
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(
		LidToestemmingRepository $lidToestemmingRepository,
		CmsPaginaRepository $cmsPaginaRepository
	) {
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
  * @throws Exception
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/toestemming', methods: ['POST'])]
 public function POST_overzicht()
	{
		$form = new ToestemmingModalForm($this->lidToestemmingRepository);

		if ($form->isPosted() && $form->validate()) {
			$this->lidToestemmingRepository->saveForLid();
			$this->addFlash(FlashType::SUCCESS, 'Toestemming opgeslagen');
			return $this->render('cms/pagina-inhoud.html.twig', [
				'pagina' => $this->cmsPaginaRepository->find('thuis'),
			]);
		} else {
			return $form;
		}
	}

	/**
  * @return Response
  * @throws Exception
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/toestemming', methods: ['GET'])]
 public function GET_overzicht(): Response
	{
		return $this->render('cms/pagina.html.twig', [
			'pagina' => $this->cmsPaginaRepository->find('thuis'),
			'modal' => new ToestemmingModalForm($this->lidToestemmingRepository),
		]);
	}

	/**
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/toestemming/annuleren', methods: ['POST'])]
 public function POST_annuleren(): Response
	{
		$_SESSION['stop_nag'] = time();

		return $this->render('cms/pagina-inhoud.html.twig', [
			'pagina' => $this->cmsPaginaRepository->find('thuis'),
		]);
	}

	/**
  * @return RedirectResponse
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/toestemming/annuleren', methods: ['GET'])]
 public function GET_annuleren(): RedirectResponse
	{
		$_SESSION['stop_nag'] = time();

		return $this->redirectToRoute('default');
	}

	/**
  * @param Request $request
  * @return ToestemmingLijstResponse|Response
  * @Auth({P_LEDEN_MOD,P_ALBUM_MOD,"commissie:promocie:ht"})
  * @throws Exception
  */
 #[Route(path: '/toestemming/lijst', methods: ['GET', 'POST'])]
 public function lijst(Request $request)
	{
		if ($this->mag(P_LEDEN_MOD)) {
			$ids = ['foto_intern', 'foto_extern', 'vereniging', 'bijzonder'];
		} elseif ($this->mag(P_ALBUM_MOD)) {
			$ids = ['foto_intern', 'foto_extern'];
		} elseif ($this->mag('commissie:promocie:ht')) {
			$ids = ['foto_intern', 'foto_extern'];
		} else {
			throw $this->createAccessDeniedException('Geen toegang');
		}

		if ($request->getMethod() === 'POST') {
			$filter = $request->query->get('filter', 'leden');

			$filterStatus = [
				'leden' => LidStatus::getLidLike(),
				'oudleden' => LidStatus::getOudlidLike(),
				'ledenoudleden' => array_merge(
					LidStatus::getLidLike(),
					LidStatus::getOudlidLike()
				),
				'iedereen' => LidStatus::getEnumValues(),
			];

			$toestemming = ArrayUtil::group_by(
				'uid',
				$this->lidToestemmingRepository->getToestemmingForIds($ids)
			);

			$toestemmingFiltered = [];
			foreach ($toestemming as $uid => $toestemmingen) {
				$profiel = ProfielRepository::get($uid);

				if (in_array($profiel->status, $filterStatus[$filter])) {
					$toestemmingFiltered[] = $toestemmingen;
				}
			}

			return new ToestemmingLijstResponse($toestemmingFiltered, $ids);
		} else {
			return $this->render('default.html.twig', [
				'content' => new ToestemmingLijstTable($ids),
			]);
		}
	}
}
