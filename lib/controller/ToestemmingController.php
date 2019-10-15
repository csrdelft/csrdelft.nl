<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\instellingen\LidToestemmingModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 *
 * @property LidToestemmingModel $model
 */
class ToestemmingController {
	use QueryParamTrait;

	public function __construct() {
		$this->model = LidToestemmingModel::instance();
	}

	/**
	 * @throws \Exception
	 */
	public function POST_overzicht() {
		$form = new ToestemmingModalForm();

		if ($form->isPosted() && $form->validate()) {

			$this->model->save();
			setMelding('Toestemming opgeslagen', 1);
			return new CmsPaginaView(CmsPaginaModel::get('thuis'));
		} else {
			return $form;
		}
	}

	/**
	 * @throws \SmartyException
	 */
	public function GET_overzicht() {
		return view('default', ['content' => new CmsPaginaView(CmsPaginaModel::get('thuis')), 'modal' => new ToestemmingModalForm()]);
	}

	public function POST_annuleren() {
		$_SESSION['stop_nag'] = time();

		return new CmsPaginaView(CmsPaginaModel::get('thuis'));
	}

	public function GET_annuleren() {
		$_SESSION['stop_nag'] = time();

		redirect('/');
	}

	public function lijst() {
		if (LoginModel::mag('P_LEDEN_MOD')) {
			$ids = ['foto_intern', 'foto_extern', 'vereniging', 'bijzonder'];
		} else if (LoginModel::mag(P_ALBUM_MOD)) {
			$ids = ['foto_intern', 'foto_extern'];
		} else {
			throw new CsrToegangException('Geen toegang');
		}

		if ($this->getMethod() === 'POST') {
		    $filter = $this->hasParam('filter') ? $this->getParam('filter') : 'leden';

		    $filterStatus = [
		        'leden' => LidStatus::getLidLike(),
                'oudleden' => LidStatus::getOudlidLike(),
                'ledenoudleden' => array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike()),
                'iedereen' => LidStatus::getTypeOptions(),
            ];

            $toestemming = group_by('uid', LidToestemmingModel::instance()->getToestemmingForIds($ids));

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
