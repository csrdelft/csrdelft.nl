<?php

namespace CsrDelft\view\courant;

use CsrDelft\model\CourantBerichtModel;
use CsrDelft\model\CourantModel;
use CsrDelft\view\SmartyTemplateView;
use Exception;
use SmartyException;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CourantBeheerView extends SmartyTemplateView {
	/** @var CourantBerichtFormulier */
	private $form;

	public function __construct(CourantModel $courant, $form) {
		parent::__construct($courant, 'C.S.R.-courant');

		$this->form = $form;
	}

	public function getBreadcrumbs() {
		$breadcrumbs = '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>';

		if ($this->form->getModel()->titel) {
			$breadcrumbs .= '<li class="breadcrumb-item"><a href="/courant">Courant</a></li>'
				. '<li class="breadcrumb-item">' . $this->form->getModel()->titel . '</li>';
		} else {
			$breadcrumbs .= '<li class="breadcrumb-item">Courant</li>';
		}
		return $breadcrumbs;
	}

	/**
	 * @throws SmartyException
	 * @throws Exception
	 */
	public function view() {
		//als er gepost is de meuk uit post halen.
		$this->smarty->assign('courant', $this->model);
		$this->smarty->assign('berichten', CourantBerichtModel::instance()->getBerichtenVoorGebruiker());
		$this->smarty->assign('form', $this->form);
		$this->smarty->assign('sponsor', 'https://www.csrdelft.nl/plaetjes/banners/' . instelling('courant', 'sponsor'));
		$this->smarty->display('courant/courantbeheer.tpl');
	}

}
