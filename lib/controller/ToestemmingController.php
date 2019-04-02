<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 *
 * @property LidToestemmingModel $model
 */
class ToestemmingController extends AclController {
	public function __construct($query) {
		parent::__construct($query, LidToestemmingModel::instance());

		$this->acl = [
			'overzicht' => P_LOGGED_IN,
			'annuleren' => P_LOGGED_IN,
			'lijst' => P_LOGGED_IN,
		];
	}

	/**
	 * @param array $args
	 * @return mixed
	 * @throws \CsrDelft\common\CsrException
	 */
	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'overzicht';
		}
		return parent::performAction($args);
	}

	/**
	 * @throws \Exception
	 */
	public function POST_overzicht() {
		$form = new ToestemmingModalForm();

		if ($form->validate()) {

			$this->model->save();
			setMelding('Toestemming opgeslagen', 1);
			$this->view = new CmsPaginaView(CmsPaginaModel::get('thuis'));
		} else {
			$this->view = $form;
		}
	}

	/**
	 * @throws \SmartyException
	 */
	public function GET_overzicht() {
		$this->view = new CsrLayoutPage(new CmsPaginaView(CmsPaginaModel::get('thuis')), [], new ToestemmingModalForm());
	}

	public function POST_annuleren() {
		$_SESSION['stop_nag'] = time();

		$this->view = new CmsPaginaView(CmsPaginaModel::get('thuis'));
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
		        'leden' => ['S_NOVIET', 'S_LID', 'S_GASTLID'],
                'oudleden' => ['S_OUDLID', 'S_ERELID'],
                'ledenoudleden' => ['S_NOVIET', 'S_LID', 'S_GASTLID', 'S_OUDLID', 'S_ERELID'],
                'iedereen' => ['S_NOVIET', 'S_LID', 'S_GASTLID', 'S_OUDLID', 'S_ERELID', 'S_KRINGEL', 'S_EXLID', 'S_OVERLEDEN'],
            ];

            $toestemming = group_by('uid', LidToestemmingModel::instance()->getToestemmingForIds($ids));

            $toestemmingFiltered = [];
            foreach ($toestemming as $uid => $toestemmingen) {
                $profiel = ProfielModel::get($uid);

                if (in_array($profiel->status, $filterStatus[$filter])) {
                    $toestemmingFiltered[] = $toestemmingen;
                }
            }

            $this->view = new ToestemmingLijstResponse($toestemmingFiltered, $ids);
        } else {
            $this->view = view('pagina', [
                'titel' => 'Lid toestemming',
                'breadcrumbs' => 'Lid toestemmingen',
                'body' => new ToestemmingLijstTable($ids)
            ]);
        }
	}
}
