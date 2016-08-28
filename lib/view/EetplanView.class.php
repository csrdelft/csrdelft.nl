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


class EetplanBekendenTable extends DataTable {
    public function __construct() {
        parent::__construct(EetplanBekendenModel::orm, '/eetplan/novietrelatie', 'Novieten die elkaar kennen');
        $this->settings['tableTools']['aButtons'] = array('select_all', 'select_none', 'copy', 'xls', 'pdf');
        $this->addColumn('noviet1');
        $this->addColumn('noviet2');
        $this->searchColumn('noviet1');
        $this->searchColumn('noviet2');
        $this->addColumn('Verwijderen', null, '<input type="button" value="Verwijderen"/>');

        $this->addKnop(new DataTableKnop("== 0", $this->dataTableId, '/eetplan/novietrelatie/toevoegen', 'post popup', 'Toevoegen', 'Bekenden toevoegen', 'cross'));
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, '/eetplan/novietrelatie/verwijderen', 'post confirm', 'Verwijderen', 'Bekenden verwijderen', 'cross'));
    }
}

class EetplanBekendenForm extends ModalForm {
    function __construct(EetplanBekenden $model) {
        parent::__construct($model, '/eetplan/novietrelatie/toevoegen', false, true);
        $fields[] = new RequiredLidField('uid1', $model->uid1, 'Noviet 1', 'novieten');
        $fields[] = new RequiredLidField('uid2', $model->uid2, 'Noviet 2', 'novieten');
        $fields['btn'] = new FormDefaultKnoppen();

        $this->addFields($fields);
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
        $bekendenTable = new EetplanBekendenTable();
        $this->smarty->assign("woonoorden", $this->woonoorden);
        $this->smarty->assign("eetplan", $this->eetplan);
        $this->smarty->assign("bekendentable", $bekendenTable);
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

class EetplanRelatieView extends DataTableResponse {
    public function getJson($entity) {
        $array = $entity->jsonSerialize();
        $array['noviet1'] = $entity->getNoviet1()->getNaam();
        $array['noviet2'] = $entity->getNoviet2()->getNaam();
        return parent::getJson($array);
    }
}
