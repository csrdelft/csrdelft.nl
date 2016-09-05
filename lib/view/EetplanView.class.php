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
    public function __construct(EetplanModel $model) {
        parent::__construct($model);
        $this->eetplan = $this->model->getEetplan();
    }

    public function getBreadcrumbs() {
        return parent::getBreadcrumbs() . ' » <span>Beheer</span>';
    }

    public function view() {
        $this->smarty->assign("bekendentable", new EetplanBekendenTable());
        $this->smarty->assign("huizentable", new EetplanHuizenTable()); // TODO: consistentie huizen-woonoorden
        $this->smarty->assign("bekendehuizentable", new EetplanBekendeHuizenTable());
        $this->smarty->display('eetplan/beheer.tpl');
    }
}

class EetplanHuizenTable extends DataTable {
    public function __construct() {
        parent::__construct('EetplanHuizenData', '/eetplan/woonoorden/', 'Woonoorden die meedoen');
        $this->searchColumn('naam');
        $this->addColumn('eetplan', null, null, 'switchButton_' . $this->dataTableId);
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'aan', 'post', 'Aanmelden', 'Woonoorden aanmelden voor eetplan', 'text'));
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'uit', 'post', 'Afmelden', 'Woonoorden afmelden voor eetplan', 'text'));
    }

    public function getJavascript() {
        return parent::getJavascript() . <<<JS
function switchButton_{$this->dataTableId} (data) {
    return '<span class="ico '+(data?'tick':'cross')+'"></span>';
}
JS;

    }
}

class EetplanHuizenData {
    public function getPrimaryKey() {
        return array('id');
    }

    public function getAttributes() {
        return array('id', 'naam', 'soort', 'eetplan');
    }
}

/**
 * Data voor EetplanHuizenTable op /eetplan/woonoorden
 *
 * Class EetplanHuizenView
 */
class EetplanHuizenView extends DataTableResponse {
    public function getJson($entity) {
        return parent::getJson(array(
            'UUID' => $entity->getUUID(),
            'id' => $entity->id,
            'naam' => $entity->naam,
            'soort' => $entity->soort,
            'eetplan' => $entity->eetplan
        ));
    }
}

class EetplanBekendenTable extends DataTable {
    public function __construct() {
        parent::__construct(EetplanBekendenModel::orm, '/eetplan/novietrelatie/', 'Novieten die elkaar kennen');
        $this->addColumn('noviet1');
        $this->addColumn('noviet2');
        $this->searchColumn('noviet1');
        $this->searchColumn('noviet2');

        $this->addKnop(new DataTableKnop("== 0", $this->dataTableId, $this->dataUrl . 'toevoegen', 'post popup', 'Toevoegen', 'Bekenden toevoegen', 'text', 'add'));
        $this->addKnop(new DataTableKnop(">= 1", $this->dataTableId, $this->dataUrl . 'verwijderen', 'post confirm', 'Verwijderen', 'Bekenden verwijderen', 'text', 'cross'));
    }
}

/**
 * View voor EetplanBekendenTable op /eetplan/novietrelatie
 *
 * Class EetplanRelatieView
 */
class EetplanRelatieView extends DataTableResponse {
    public function getJson($entity) {
        $array = $entity->jsonSerialize();
        $array['noviet1'] = $entity->getNoviet1()->getNaam();
        $array['noviet2'] = $entity->getNoviet2()->getNaam();
        return parent::getJson($array);
    }
}

/**
 * Toevoegen voor EetplanBekendenTable op /eetplan/novietrelatie/toevoegen
 *
 * Class EetplanBekendenForm
 */
class EetplanBekendenForm extends ModalForm {
    function __construct(EetplanBekenden $model) {
        parent::__construct($model, '/eetplan/novietrelatie/toevoegen', false, true);
        $fields[] = new RequiredLidField('uid1', $model->uid1, 'Noviet 1', 'novieten');
        $fields[] = new RequiredLidField('uid2', $model->uid2, 'Noviet 2', 'novieten');
        $fields['btn'] = new FormDefaultKnoppen();

        $this->addFields($fields);
    }
}


class EetplanBekendeHuizenTable extends DataTable {
    public function __construct() {
        parent::__construct(EetplanModel::orm, '/eetplan/bekendehuizen/', 'Novieten die huizen kennen');
        $this->hideColumn('avond');
        $this->hideColumn('woonoord_id');
        $this->hideColumn('uid');
        $this->addColumn('woonoord');
        $this->addColumn('naam');

        $this->addKnop(new DataTableKnop("== 0", $this->dataTableId, $this->dataUrl . 'toevoegen', 'post popup', 'Toevoegen', 'Bekende toevoegen', 'text', 'add'));
        $this->addKnop(new DataTableKnop("== 1", $this->dataTableId, $this->dataUrl . 'verwijderen', 'post confirm', 'Verwijderen', 'Bekende verwijderen', 'text', 'cross'));
    }
}

/**
 * Data voor EetplanBekendeHuizenTable op /eetplan/bekendehuizen
 *
 * Class EetplanBekendeHuizenResponse
 */
class EetplanBekendeHuizenResponse extends DataTableResponse {
    public function getJson($entity) {
        $array = $entity->jsonSerialize();
        $array['woonoord'] = $entity->getWoonoord()->naam;
        $array['naam'] = $entity->getNoviet()->getNaam();

        return parent::getJson($array);
    }
}

/**
 * Formulier voor noviet-huis relatie tovoegen op /eetplan/bekendehuizen/toevoegen
 *
 * Class EetplanBekendeHuizenForm
 */
class EetplanBekendeHuizenForm extends ModalForm {
    public function __construct($model) {
        parent::__construct($model, '/eetplan/bekendehuizen/toevoegen', false, true);
        $fields[] = new RequiredLidField('uid', $model->uid, 'Noviet', 'novieten');
        $fields[] = new RequiredEntityField('woonoord', 'naam', 'Woonoord', WoonoordenModel::instance(), '/eetplan/bekendehuizen/zoeken?q=');

        $fields['btn'] = new FormDefaultKnoppen();

        $this->addFields($fields);
    }
}

/**
 * Typeahead response voor EetplanBekendeHuizenForm op /eetplan/bekendehuizen/zoeken
 *
 * Class EetplanHuizenResponse
 */
class EetplanHuizenResponse extends JsonLijstResponse  {

    public function getJson($entity) {
        return parent::getJson(array(
            'url' => $entity->getUrl(),
            'label' => $entity->id,
            'value' => $entity->naam,
            'id' => $entity->id,
        ));
    }
}