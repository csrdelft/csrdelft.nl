<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingController extends AbstractController {
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(LidToestemmingRepository $lidToestemmingRepository, CmsPaginaRepository $cmsPaginaRepository) {
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
	 * @throws Exception
	 */
	public function POST_overzicht() {
		$form = new ToestemmingModalForm($this->lidToestemmingRepository);

		if ($form->isPosted() && $form->validate()) {

			$this->lidToestemmingRepository->saveForLid();
			setMelding('Toestemming opgeslagen', 1);
			return new CmsPaginaView($this->cmsPaginaRepository->find('thuis'));
		} else {
			return $form;
		}
	}

	public function GET_overzicht() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('thuis')), 'modal' => new ToestemmingModalForm($this->lidToestemmingRepository)]);
	}

	public function POST_annuleren() {
		$_SESSION['stop_nag'] = time();

		return new CmsPaginaView($this->cmsPaginaRepository->find('thuis'));
	}

	public function GET_annuleren() {
		$_SESSION['stop_nag'] = time();

		return $this->redirectToRoute('default');
	}

	public function lijst(Request $request) {
		if (LoginService::mag('P_LEDEN_MOD')) {
			$ids = ['foto_intern', 'foto_extern', 'vereniging', 'bijzonder'];
		} else if (LoginService::mag(P_ALBUM_MOD)) {
			$ids = ['foto_intern', 'foto_extern'];
		} else {
			throw new CsrToegangException('Geen toegang');
		}

		if ($request->getMethod() === 'POST') {
			$filter = $request->query->get('filter', 'leden');

		    $filterStatus = [
		        'leden' => LidStatus::getLidLike(),
                'oudleden' => LidStatus::getOudlidLike(),
                'ledenoudleden' => array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike()),
                'iedereen' => LidStatus::getTypeOptions(),
            ];

            $toestemming = group_by('uid', $this->lidToestemmingRepository->getToestemmingForIds($ids));

            $toestemmingFiltered = [];
            foreach ($toestemming as $uid => $toestemmingen) {
                $profiel = ProfielRepository::get($uid);

                if (in_array($profiel->status, $filterStatus[$filter])) {
                    $toestemmingFiltered[] = $toestemmingen;
                }
            }

            return new ToestemmingLijstResponse($toestemmingFiltered, $ids);
        } else {
            return view('pagina', [
                'titel' => 'Lid toestemming',
                'breadcrumbs' => 'Lid toestemmingen',
                'body' => new ToestemmingLijstTable($ids)
            ]);
        }
	}
}
