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
		$this->assign('profiel', $this->profiel);
		$this->assign('melding', $this->getMelding());
		$this->assign('actie', $this->actie);
		$this->display('profiel/wijzigvoorkeur.tpl');
	}

}

?>