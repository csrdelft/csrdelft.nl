<?php

class MededelingContent extends SmartyTemplateView {

	private $prullenbak;

	public function __construct(Mededeling $mededeling, $prullenbak = false) {
		parent::__construct($mededeling, 'Mededelingen');
		$this->prullenbak = $prullenbak;
	}

	public function view() {
		$this->smarty->assign('mededeling', $this->model);
		$this->smarty->assign('prullenbak', $this->prullenbak);
		$this->smarty->assign('prioriteiten', Mededeling::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->model->getVervaltijd() === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}
		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}
