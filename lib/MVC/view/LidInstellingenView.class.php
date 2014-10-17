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
			echo '<div id="tabs-' . $module . '">';
			foreach ($instellingen as $key => $def) {
				$this->smarty->assign('module', $module);
				$this->smarty->assign('id', $key);
				$this->smarty->assign('type', $this->model->getType($module, $key));
				$this->smarty->assign('opties', $this->model->getTypeOptions($module, $key));
				$this->smarty->assign('label', $this->model->getDescription($module, $key));
				$this->smarty->assign('waarde', $this->model->getValue($module, $key));
				$this->smarty->assign('default', $this->model->getDefault($module, $key));
				$this->smarty->assign('reset', $reset);
				$this->smarty->display('MVC/instellingen/lidinstelling.tpl');
			}
			echo '</div>';
		}
		echo '</div>';
		$from = new UrlField('referer', HTTP_REFERER);
		$from->hidden = true;
		$from->view();
		$btns = new FormKnoppen('/');
		$btns->view();
		echo '</form>';
	}

}
