<?php

/**
 * LidInstellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LidInstellingenView extends SmartyTemplateView {

	public function __construct(LidInstellingen $model) {
		parent::__construct($model, 'Webstekinstellingen');
	}

	public function view() {
		$this->smarty->display('MVC/instellingen/lidinstellingen_page.tpl');
		echo '<form id="lidinstellingenform" action="/instellingen/opslaan" method="post" class="Formulier"><div id="tabs"><ul>';
		foreach ($this->model->getModules() as $module) {
			echo '<li><a href="#tabs-' . $module . '">' . ucfirst($module) . '</a></li>';
		}
		echo '</ul>';
		$reset = LoginModel::mag('P_ADMIN');
		foreach ($this->model->getInstellingen() as $module => $instellingen) {
			echo '<div id="tabs-' . $module . '"><br />';
			foreach ($instellingen as $id) {
				$this->smarty->assign('module', $module);
				$this->smarty->assign('id', $id);
				$this->smarty->assign('type', $this->model->getType($module, $id));
				$this->smarty->assign('opties', $this->model->getTypeOptions($module, $id));
				$this->smarty->assign('label', $this->model->getDescription($module, $id));
				$this->smarty->assign('waarde', $this->model->getValue($module, $id));
				$this->smarty->assign('default', $this->model->getDefault($module, $id));
				$this->smarty->assign('reset', $reset);
				$this->smarty->display('MVC/instellingen/lidinstelling.tpl');
			}
			echo '</div>';
		}
		echo '</div>';
		$from = new UrlField('referer', HTTP_REFERER, null);
		$from->hidden = true;
		$from->view();
		$btns = new FormDefaultKnoppen('/');
		$btns->view();
		echo '</form>';
	}

}
