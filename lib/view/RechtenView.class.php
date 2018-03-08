<?php

namespace CsrDelft\view;

use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\entity\security\AccessControl;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\DataTableResponse;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\formulier\datatable\Multiplicity;

/**
 * RechtenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RechtenTable extends DataTable {

	public function __construct(AccessModel $model, $environment, $resource) {
		parent::__construct($model::ORM, '/rechten/bekijken/' . $environment . '/' . $resource, 'Rechten voor ' . $environment . ' ' . $resource, 'resource');

		$this->hideColumn('action', false);
		$this->searchColumn('aciton');

		// Has permission to change permissions?
		if (!LoginModel::mag('P_ADMIN')) {
			$rechten = $model::getSubject($environment, AccessAction::Rechten, $resource);
			if (!$rechten OR !LoginModel::mag($rechten)) {
				return;
			}
		}

		$create = new DataTableKnop(Multiplicity::Zero(), '/rechten/aanmaken/' . $environment . '/' . $resource, 'Instellen', 'Rechten instellen', 'key_add');
		$this->addKnop($create);

		$update = new DataTableKnop(Multiplicity::One(), '/rechten/wijzigen', 'Wijzigen', 'Rechten wijzigen', 'key_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop(Multiplicity::Any(), '/rechten/verwijderen', 'Intrekken', 'Rechten intrekken', 'key_delete');
		$this->addKnop($delete);
	}

	public function view() {
		$view = new CmsPaginaView(CmsPaginaModel::get('UitlegACL'));
		$view->view();
		parent::view();
	}

}

class RechtenData extends DataTableResponse {

	/**
	 * @param AccessControl $ac
	 * @return string
	 * @throws \Exception
	 */
	public function getJson($ac) {
		$array = $ac->jsonSerialize();

		$array['action'] = AccessAction::getDescription($ac->action);

		if ($ac->resource === '*') {
			$array['resource'] = 'Elke ' . lcfirst($ac->environment);
		} else {
			$array['resource'] = 'Deze ' . lcfirst($ac->environment);
		}

		return parent::getJson($array);
	}

}

class RechtenForm extends ModalForm {

	/**
	 * RechtenForm constructor.
	 * @param AccessControl $ac
	 * @param $action
	 * @throws \Exception
	 */
	public function __construct(AccessControl $ac, $action) {
		parent::__construct($ac, '/rechten/' . $action . '/' . $ac->environment . '/' . $ac->resource, 'Rechten aanpassen voor ', true);
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
			foreach (AccessAction::getTypeOptions() as $option) {
				$acties[$option] = AccessAction::getDescription($option);
			}
			$fields[] = new SelectField('action', $ac->action, 'Actie', $acties);
		} else {
			$fields[] = new HtmlComment('<label>Actie</label><div class="dikgedrukt">' . AccessAction::getDescription($ac->action) . '</div>');
		}
		$fields[] = new RequiredRechtenField('subject', $ac->subject, 'Toegestaan voor');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
