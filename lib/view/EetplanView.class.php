<?php

require_once 'model/EetplanModel.class.php';

/**
 * EetplanView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Weergeven van eetplan.
 */
abstract class AbstractEetplanView extends SmartyTemplateView {

	protected $eetplan;

	public function getTitel() {
		return 'Eetplan';
	}

	public function getBreadcrumbs() {
		return '<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a> » <a href="/eetplan">Eetplan</a>';
	}

}

class EetplanView extends AbstractEetplanView {
	function view() {
        $this->smarty->assign('avonden', $this->model->getAvonden());
        $this->smarty->assign('eetplan', $this->model->getEetplan());
        $this->smarty->display('eetplan/overzicht.tpl');
	}
}

class EetplanNovietView extends AbstractEetplanView {

	private $uid;

	public function __construct(EetplanModel $model, $uid) {
		parent::__construct($model);
		$this->uid = $uid;
		$this->eetplan = $this->model->getEetplanVoorNoviet($this->uid);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . ProfielModel::getLink($this->uid, 'civitas');
	}

	function view() {
		//huizen voor een feut tonen
        $this->smarty->assign('eetplan', $this->eetplan);
        $this->smarty->display('eetplan/noviet.tpl');
	}

}

class EetplanHuisView extends AbstractEetplanView {

	private $woonoord;

	public function __construct(EetplanModel $model, $iHuisID) {
		parent::__construct($model);
		$this->eetplan = $this->model->getEetplanVoorHuis($iHuisID);
        $this->woonoord = WoonoordenModel::get($iHuisID);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/groepen/woonoorden/' . $this->woonoord->id . '">' . $this->woonoord->naam . '</a>';
	}

	function view() {
		//feuten voor een huis tonen
        $this->smarty->assign('model', $this->model);
        $this->smarty->assign('eetplan', $this->eetplan);
        $this->smarty->display('eetplan/huis.tpl');
	}
}

class EetplanBeheerView extends AbstractEetplanView {
    private $woonoorden;
    public function __construct(EetplanModel $model, WoonoordenModel $woonoordenModel)
    {
        parent::__construct($model);
        $this->eetplan = $this->model->getEetplan();
        $this->woonoorden = $woonoordenModel->find('status = ?', array(GroepStatus::HT))->fetchAll();
    }

    public function getBreadcrumbs() {
        return parent::getBreadcrumbs() . ' » <span>Beheer</span>';
    }

    public function view() {
        $this->smarty->assign("woonoorden", $this->woonoorden);
        $this->smarty->assign("eetplan", $this->eetplan);
        $this->smarty->display('eetplan/beheer.tpl');
    }
}

class EetplanHuisStatusView extends JsonResponse {
    public function getJson($entity)
    {
        return parent::getJson(array(
            'id' => $entity->id,
            'eetplan' => $entity->eetplan
        ));
    }
}
