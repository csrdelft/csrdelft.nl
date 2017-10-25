<?php

namespace CsrDelft\view\maalcie\corvee\taken;

use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\view\SmartyTemplateView;

class BeheerTaakView extends SmartyTemplateView {

	private $maaltijd;

	public function __construct(
		CorveeTaak $taak,
		Maaltijd $maaltijd = null
	) {
		parent::__construct($taak);
		$this->maaltijd = $maaltijd;
	}

	public function view() {
		$this->smarty->assign('taak', $this->model);
		$this->smarty->assign('maaltijd', $this->maaltijd);
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
		$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
	}

}
