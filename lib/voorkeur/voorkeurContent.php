<?php

class LidPaginaVoorkeurContent extends TemplateView {

	private $actie;

	public function __construct($lid, $actie) {
		parent::__construct();
		$this->lid = $lid;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'voorkeur van ' . $this->lid->getNaam() . ' bekijken.';
	}

	public function view() {
		$this->smarty->assign('profiel', $this->profiel);
		$this->smarty->assign('melding', $this->getMelding());
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('profiel/wijzigvoorkeur.tpl');
	}

}

?>