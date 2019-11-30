<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\instellingen\LidToestemmingModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingController extends AbstractController {
	/**
	 * @var LidToestemmingModel
	 */
	private $lidToestemmingModel;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(LidToestemmingModel $lidToestemmingModel, CmsPaginaRepository $cmsPaginaRepository) {
		$this->lidToestemmingModel = $lidToestemmingModel;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
	 * @throws \Exception
	 */
	public function POST_overzicht() {
		$form = new ToestemmingModalForm();

		if ($form->isPosted() && $form->validate()) {

			$this->lidToestemmingModel->save();
			setMelding('Toestemming opgeslagen', 1);
			return new CmsPaginaView($this->cmsPaginaRepository->find('thuis'));
		} else {
			return $form;
		}
	}

	public function GET_overzicht() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('thuis')), 'modal' => new ToestemmingModalForm()]);
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
		if (LoginModel::mag('P_LEDEN_MOD')) {
			$ids = ['foto_intern', 'foto_extern', 'vereniging', 'bijzonder'];
		} else if (LoginModel::mag(P_ALBUM_MOD)) {
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

            $toestemming = group_by('uid', $this->lidToestemmingModel->getToestemmingForIds($ids));

            $toestemmingFiltered = [];
            foreach ($toestemming as $uid => $toestemmingen) {
                $profiel = ProfielModel::get($uid);

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
