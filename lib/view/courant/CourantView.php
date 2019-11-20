<?php

namespace CsrDelft\view\courant;

use CsrDelft\common\CsrException;
use CsrDelft\common\Ini;
use CsrDelft\model\CourantModel;
use CsrDelft\model\entity\courant\Courant;
use CsrDelft\model\entity\courant\CourantCategorie;
use CsrDelft\view\SmartyTemplateView;
use Exception;
use SmartyException;

/**
 * CourantView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @property Courant $model
 *
 */
class CourantView extends SmartyTemplateView {

	private $berichten;
	private $instellingen;

	/**
	 * CourantView constructor.
	 * @param Courant $courant
	 * @throws CsrException
	 */
	public function __construct(Courant $courant, $berichten) {
		parent::__construct($courant);
		setlocale(LC_ALL, 'nl_NL@euro');
		$this->instellingen = Ini::lees(Ini::CSRMAIL);
		$this->berichten = $berichten;
	}

	public function getTitel() {
		return 'C.S.R.-courant van ' . $this->getVerzendMoment();
	}

	public function getVerzendMoment() {
		return strftime('%d %B %Y', strtotime($this->model->verzendMoment));
	}

	public function getHtml($headers = false) {
		$this->smarty->assign('instellingen', $this->instellingen);
		$this->smarty->assign('courant', $this->model);
		$this->smarty->assign('berichten', $this->berichten);
		$this->smarty->assign('catNames', CourantCategorie::getSelectOptions());
		$this->smarty->assign('headers', $headers);

		return $this->smarty->fetch('courant/mail/courant.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}
}
