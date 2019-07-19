<?php

namespace CsrDelft\view\maalcie\persoonlijk;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\view\SmartyTemplateView;


/**
 * MaaltijdKetzerView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van een ketzer voor een specifieke maaltijd waarmee een lid zich kan aan- of afmelden voor die maaltijd.
 *
 */
class MaaltijdKetzerView extends SmartyTemplateView {

	private $aanmelding;

	public function __construct(Maaltijd $maaltijd, $aanmelding = null) {
		parent::__construct($maaltijd, 'Maaltijdketzer');
		$this->aanmelding = $aanmelding;
	}

	public function getHtml() {
		$this->smarty->assign('standaardprijs', intval(instelling('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $this->aanmelding);
		return $this->smarty->fetch('maalcie/maaltijd/maaltijd_ketzer.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}
