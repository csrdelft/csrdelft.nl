<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\security\AccessControl;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\security\AccessRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * RechtenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RechtenTable extends DataTable {

	public function __construct(AccessRepository $model, $environment, $resource) {
		parent::__construct(AccessControl::class, '/rechten/bekijken/' . $environment . '/' . $resource, 'Rechten voor ' . $environment . ' ' . $resource, 'resource');

		$this->hideColumn('action', false);
		$this->searchColumn('aciton');

		// Has permission to change permissions?
		if (!LoginService::mag(P_ADMIN)) {
			$rechten = $model->getSubject($environment, AccessAction::Rechten, $resource);
			if (!$rechten OR !LoginService::mag($rechten)) {
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

	public function __toString() {
		$view = new CmsPaginaView(ContainerFacade::getContainer()->get(CmsPaginaRepository::class)->find('UitlegACL'));
		return $view->__toString() . parent::__toString();
	}

}
