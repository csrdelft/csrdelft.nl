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
