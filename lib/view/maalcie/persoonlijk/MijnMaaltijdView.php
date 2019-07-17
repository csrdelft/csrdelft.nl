<?php

namespace CsrDelft\view\maalcie\persoonlijk;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\view\SmartyTemplateView;

class MijnMaaltijdView extends SmartyTemplateView {

	private $aanmelding;

	public function __construct(
		Maaltijd $maaltijd,
		MaaltijdAanmelding $aanmelding = null
	) {
		parent::__construct($maaltijd);
		$this->aanmelding = $aanmelding;
	}

	public function view() {
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $this->aanmelding);
		$this->smarty->assign('standaardprijs', intval(instelling('maaltijden', 'standaard_prijs')));
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijd_lijst.tpl');
	}

}
