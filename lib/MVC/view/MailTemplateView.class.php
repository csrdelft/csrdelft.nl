<?php

/**
 * MailTemplateView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MailTemplateView extends TemplateView {

	public function __construct(Mail $model) {
		parent::__construct($model);
	}

	public function getBody() {
		$this->smarty->assign('body', $this->model->getBody());
		return $this->smarty->fetch('MVC/mail/layout/' . $this->model->getLayout() . '.tpl');
	}

	public function view() {
		echo $this->getBody();
	}

}
