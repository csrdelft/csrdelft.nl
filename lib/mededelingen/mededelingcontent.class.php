<?php

class MededelingContent extends TemplateView {

	private $mededeling;
	private $prullenbak;

	public function __construct(Mededeling $mededeling, $prullenbak = false) {
		parent::__construct();
		$this->mededeling = $mededeling;
		$this->prullenbak = $prullenbak;
	}

	public function getTitel() {
		return 'Mededelingen';
	}

	public function view() {
		define('NIEUWS_ROOT', '/actueel/mededelingen/');

		$this->smarty->assign('mededeling', $this->mededeling);
		$this->smarty->assign('prullenbak', $this->prullenbak);
		$this->smarty->assign('nieuws_root', NIEUWS_ROOT);
		$this->smarty->assign('prioriteiten', Mededeling::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		$this->smarty->assign('aantalTopMostBlock', MededelingenContent::aantalTopMostBlock);

		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->mededeling->getVervaltijd() === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}

		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}
