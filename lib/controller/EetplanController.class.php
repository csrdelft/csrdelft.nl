<?php

require_once 'model/EetplanModel.class.php';
require_once 'view/EetplanView.class.php';

/**
 * EetplanController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor eetplan.
 */
class EetplanController extends AclController {
    /**
     * @var EetplanModel
     */
    protected $model;

    public function __construct($query) {
        parent::__construct($query, new EetplanModel('15'));
        if ($this->isPosted()) {
            $this->acl = array(
                'beheer' => 'P_ADMIN',
                'woonoorden' => 'P_ADMIN',
                'novietrelatie' => 'P_ADMIN',
                'bekendehuizen' => 'P_ADMIN'
            );
        } else {
            $this->acl = array(
                'view' => 'P_LEDEN_READ',
                'noviet' => 'P_LEDEN_READ',
                'huis' => 'P_LEDEN_READ',
                'beheer' => 'P_ADMIN',
                'bekendehuizen' => 'P_ADMIN',
            );
        }
    }

	public function performAction(array $args = array()) {
		$this->action = 'view';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function view() {
		$body = new EetplanView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

	public function noviet($uid = null) {
	    $eetplan = $this->model->getEetplanVoorNoviet($uid);
        if ($eetplan === false) {
            $this->geentoegang();
        }
		$body = new EetplanNovietView($this->model, $uid);
		$this->view = new CsrLayoutPage($body);
	}

	public function huis($id = null) {
	    $eetplan = $this->model->getEetplanVoorHuis($id);
        if ($eetplan === false) {
            $this->geentoegang();
        }
		$body = new EetplanHuisView($this->model, $id);
		$this->view = new CsrLayoutPage($body);
	}

    public function woonoorden($actie = null) {
        if ($actie == 'aan') {
            $selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
            $woonoorden = array();
            foreach ($selection as $woonoord) {
                $woonoord = WoonoordenModel::instance()->getUUID($woonoord);
                $woonoord->eetplan = true;
                WoonoordenModel::instance()->update($woonoord);
                $woonoorden[] = $woonoord;
            }
            $this->view = new EetplanHuizenView($woonoorden);
            return;
        } elseif ($actie == 'uit') {
            $selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
            $woonoorden = array();
            foreach ($selection as $woonoord) {
                $woonoord = WoonoordenModel::instance()->getUUID($woonoord);
                $woonoord->eetplan = false;
                WoonoordenModel::instance()->update($woonoord);
                $woonoorden[] = $woonoord;
            }
            $this->view = new EetplanHuizenView($woonoorden);
            return;
        }

        if ($this->isPosted()) {
            $woonoorden = WoonoordenModel::instance()->find('status = ?', array(GroepStatus::HT));
            $this->view = new EetplanHuizenView($woonoorden);
        }
    }

    public function bekendehuizen($actie = null) {
        if ($actie == 'zoeken') {
            $huisnaam = filter_input(INPUT_GET, 'q');
            $huisnaam = '%' . $huisnaam . '%';
            $woonoorden = WoonoordenModel::instance()->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
            $this->view = new EetplanHuizenResponse($woonoorden);
        } elseif ($actie == 'toevoegen') {
            $eetplan = new Eetplan();
            $eetplan->avond = '0000-00-00';
            $form = new EetplanBekendeHuizenForm($eetplan);
            if ($form->validate()) {
                $this->model->create($eetplan);
                $this->view = new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen());
            } else {
                $this->view = $form;
            }

        } elseif ($this->isPosted()) {
            $this->view = new EetplanBekendeHuizenResponse($this->model->getBekendeHuizen());
        }
    }


    public function novietrelatie($actie = null) {

        if ($actie == 'toevoegen') {
            $uid1 = filter_input(INPUT_POST, 'uid1');
            $uid2 = filter_input(INPUT_POST, 'uid2');
            $bekenden = new EetplanBekenden();
            $bekenden->uid1 = namen2uid($uid1)[0]['uid'];
            $bekenden->uid2 = namen2uid($uid2)[0]['uid'];
            $form = new EetplanBekendenForm($bekenden);
            if ($form->validate()) {
                $this->model->getBekendenModel()->create($bekenden);
                $this->view = new EetplanRelatieView($this->model->getBekendenModel()->getBekenden());
            } else {
                $this->view = $form;
            }
        } elseif ($actie == 'verwijderen') {
            $selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
            $verwijderd = array();
            foreach ($selection as $uuid) {
                $uids = explode(".", explode("@", $uuid)[0]);
                $uid1 = $uids[0];
                $uid2 = $uids[1];
                $bekenden = new EetplanBekenden();
                $bekenden->uid1 = namen2uid($uid1)[0]['uid'];
                $bekenden->uid2 = namen2uid($uid2)[0]['uid'];
                $this->model->getBekendenModel()->delete($bekenden);
                $verwijderd[] = $bekenden;
            }
            $this->view = new RemoveRowsResponse($verwijderd);
        } else {
            $this->view = new EetplanRelatieView($this->model->getBekendenModel()->getBekenden());
        }
    }

    /**
     * Beheerpagina.
     *
     * POST een json body om dingen te doen.
     */
    public function beheer() {
        $body = new EetplanBeheerView($this->model, WoonoordenModel::instance());
        $this->view = new CsrLayoutPage($body);
        $this->view->addCompressedResources('eetplan');
    }
}
