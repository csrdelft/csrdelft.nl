<?php

/**
 * RechtenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class RechtenTable extends DataTable {

	public function __construct(AccessModel $model, $environment, $resource) {
		parent::__construct($model::orm, 'Rechten voor ' . $resource, 'resource');
		$this->dataUrl = '/' . A::Rechten . '/' . A::Bekijken . '/' . $environment . '/' . $resource;

		$this->hideColumn('action', false);
		$this->searchColumn('aciton');

		$create = new DataTableKnop('== 0', $this->tableId, '/' . A::Rechten . '/' . A::Aanmaken . '/' . $environment . '/' . $resource, 'post popup', 'Geven', 'Rechten uitdelen', 'key_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, '/' . A::Rechten . '/' . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig rechten', 'key_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, '/' . A::Rechten . '/' . A::Verwijderen, 'post confirm', 'Terugtrekken', 'Rechten terugtrekken', 'key_delete');
		$this->addKnop($delete);
	}

	public function view() {
		require_once 'model/CmsPaginaModel.class.php';
		require_once 'view/CmsPaginaView.class.php';
		$view = new CmsPaginaView(CmsPaginaModel::get('UitlegACL'));
		$view->view();
		parent::view();
	}

}

class RechtenData extends DataTableResponse {

	public function getJson($ac) {
		$array = $ac->jsonSerialize();

		$array['resource'] = $ac->resource === '*' ? 'Geërfd' : null;

		return parent::getJson($array);
	}

}

class RechtenForm extends DataTableForm {

	public function __construct(AccessControl $ac, $action) {
		parent::__construct($ac, '/' . A::Rechten . '/' . $action . '/' . $ac->environment . '/' . $ac->resource, 'Rechten aanpassen voor ');
		if ($ac->resource === '*') {
			$this->titel .= 'elke ';
		}
		$this->titel .= $ac->resource;

		if ($action === A::Aanmaken) {
			$acties = array();
			foreach (A::getTypeOptions() as $option) {
				$acties[$option] = A::getDescription($option);
			}
			$fields[] = new SelectField('action', $ac->action, 'Actie', $acties);
		} else {
			$fields['a'] = new TextField('action', $ac->action, 'Actie');
			$fields['a']->readonly = true;
		}
		$fields[] = new RequiredRechtenField('subject', $ac->subject, 'Rechten');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
