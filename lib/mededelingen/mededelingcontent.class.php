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

		$this->assign('mededeling', $this->mededeling);
		$this->assign('prullenbak', $this->prullenbak);
		$this->assign('melding', $this->getMelding());
		$this->assign('nieuws_root', NIEUWS_ROOT);
		$this->assign('prioriteiten', Mededeling::getPrioriteiten());
		$this->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		$this->assign('aantalTopMostBlock', MededelingenContent::aantalTopMostBlock);

		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->mededeling->getVervaltijd() === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->assign('standaardVervaltijd', $standaardVervaltijd);
		}

		$this->display('mededelingen/mededeling.tpl');
	}

}

?>