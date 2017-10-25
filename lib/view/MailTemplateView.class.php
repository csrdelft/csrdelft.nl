<?php

namespace CsrDelft\view;

use CsrDelft\model\entity\Mail;

/**
 * MailTemplateView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MailTemplateView extends SmartyTemplateView {

	public function __construct(Mail $model) {
		parent::__construct($model);
	}

	public function getHtml() {
		$this->smarty->assign('body', $this->model->getBody());
		return $this->smarty->fetch('mail/layout/' . $this->model->getLayout() . '.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}
