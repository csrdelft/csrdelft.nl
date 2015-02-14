<?php

/**
 * RechtenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class RechtenTable extends DataTable {

	public function __construct(AccessModel $model, $environment, $resource) {
		parent::__construct($model::orm, '/rechten/bekijken/' . $environment . '/' . $resource, 'Rechten voor ' . $environment . ' ' . $resource, 'resource');

		$this->hideColumn('action', false);
		$this->searchColumn('aciton');

		// Has permission to change permissions?
		if (!LoginModel::mag('P_ADMIN')) {
			$rechten = $model::getSubject($environment, A::Rechten, $resource);
			if (!$rechten OR ! LoginModel::mag($rechten)) {
				return;
			}
		}

		$create = new DataTableKnop('== 0', $this->dataTableId, '/rechten/aanmaken/' . $environment . '/' . $resource, 'post popup', 'Instellen', 'Rechten instellen', 'key_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->dataTableId, '/rechten/wijzigen', 'post popup', 'Wijzigen', 'Rechten wijzigen', 'key_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->dataTableId, '/rechten/verwijderen', 'post confirm', 'Terugtrekken', 'Rechten terugtrekken', 'key_delete');
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

		$array['action'] = A::getDescription($ac->action);

		if ($ac->resource === '*') {
			$array['resource'] = 'Elke ' . lcfirst($ac->environment);
		} else {
			$array['resource'] = 'Deze ' . lcfirst($ac->environment);
		}

		return parent::getJson($array);
	}

}

class RechtenForm extends ModalForm {

	public function __construct(AccessControl $ac, $action) {
		parent::__construct($ac, '/rechten/' . $action . '/' . $ac->environment . '/' . $ac->resource, 'Rechten aanpassen voor ');
		$this->dataTableId = true;
		if ($ac->resource === '*') {
			$this->titel .= 'elke ' . $ac->environment;
		} else {
			$this->titel .= $ac->environment . ' ' . $ac->resource;
		}

		if ($action === 'aanmaken') {

			if (LoginModel::mag('P_ADMIN')) {
				$fields[] = new RequiredTextField('environment', $ac->environment, 'Klasse');
				$fields[] = new RequiredTextField('resource', $ac->resource, 'Object');
			}

			$acties = array();
			foreach (A::getTypeOptions() as $option) {
				$acties[$option] = A::getDescription($option);
			}
			$fields[] = new SelectField('action', $ac->action, 'Actie', $acties);
		} else {
			$fields[] = new HtmlComment('<label>Actie</label><div class="dikgedrukt">' . A::getDescription($ac->action) . '</div>');
		}
		$fields[] = new RequiredRechtenField('subject', $ac->subject, 'Toegestaan voor');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
