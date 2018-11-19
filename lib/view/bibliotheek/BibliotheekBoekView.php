<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\bibliotheek\BoekRecensie;
use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

/**
 * Boek weergeven
 */
class BibliotheekBoekView extends SmartyTemplateView {
	/**
	 * @var BoekFormulier
	 */
	public $boekFormulier;
	/**
	 * @var RecensieFormulier
	 */
	public $recensieFormulier;
	/**
	 * @var BoekRecensie[]
	 */
	public $recensies;
	/**
	 * @var BoekExemplaarFormulier[]
	 */
	public $exemplaarFormulieren;

	/**
	 * BibliotheekBoekContent constructor.
	 * @param Boek $boek
	 * @param BoekFormulier $boekFormulier
	 * @param BoekRecensie[] $recensies
	 * @param RecensieFormulier $recensieFormulier
	 * @param BoekExemplaarFormulier[] $exemplaarFormulieren
	 */
	public function __construct(Boek $boek, BoekFormulier $boekFormulier, array $recensies, RecensieFormulier $recensieFormulier, array $exemplaarFormulieren) {
		parent::__construct($boek);
		$this->boekFormulier = $boekFormulier;
		$this->recensieFormulier = $recensieFormulier;
		$this->recensies = $recensies;
		$this->exemplaarFormulieren = $exemplaarFormulieren;

	}

	public function getTitel() {
		return 'Bibliotheek - Boek: ' . $this->model->getTitel();
	}

	public function view() {
		$this->smarty->assign('boek', $this->model);
		$this->smarty->assign('recensies', $this->recensies);
		$this->smarty->assign('boekFormulier', $this->boekFormulier);
		$this->smarty->assign('recensieFormulier', $this->recensieFormulier);
		$this->smarty->assign('exemplaarFormulieren', $this->exemplaarFormulieren);
		$this->smarty->display('bibliotheek/boek.tpl');
	}


}
