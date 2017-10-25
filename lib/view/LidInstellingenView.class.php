<?php

namespace CsrDelft\view;

use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\TabsForm;
use CsrDelft\view\login\LoginSessionsTable;
use CsrDelft\view\login\RememberLoginTable;

/**
 * LidInstellingenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property LidInstellingenModel $model
 */
class LidInstellingenView extends TabsForm {

	public function __construct(LidInstellingenModel $model) {
		parent::__construct($model, '/instellingen/opslaan', 'Webstekinstellingen');
		$this->formId = 'lidinstellingenform';
		$this->vertical = true;
		$this->hoverintent = true;

		$fields[] = new HtmlComment('<p>Op deze pagina kunt u diverse instellingen voor de stek wijzigen. De waarden tussen haakjes zijn de standaardwaarden.</p>');
		$this->addFields($fields, 'head');

		$smarty = CsrSmarty::instance();
		$reset = LoginModel::mag('P_ADMIN');
		foreach ($this->model->getInstellingen() as $module => $instellingen) {
			$fields = array();

			foreach ($instellingen as $id) {
				$smarty->assign('module', $module);
				$smarty->assign('id', $id);
				$smarty->assign('type', $this->model->getType($module, $id));
				$smarty->assign('opties', $this->model->getTypeOptions($module, $id));
				$smarty->assign('label', $this->model->getDescription($module, $id));
				$smarty->assign('waarde', $this->model->getValue($module, $id));
				$smarty->assign('default', $this->model->getDefault($module, $id));
				$smarty->assign('reset', $reset);
				$fields[] = new HtmlComment($smarty->fetch('instellingen/lidinstelling.tpl'));
			}
			$this->addFields($fields, ucfirst($module));
		}
		$this->addFields(array(new RememberLoginTable()), 'Beveiliging');
		$this->addFields(array(new LoginSessionsTable()), 'Beveiliging');

		$fields = array();

		$fields['r'] = new UrlField('referer', HTTP_REFERER, null);
		$fields['r']->hidden = true;

		$fields[] = new FormDefaultKnoppen('/');

		$this->addFields($fields, 'foot');
	}

}
