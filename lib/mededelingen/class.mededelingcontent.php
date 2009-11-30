<?php
class MededelingContent extends SimpleHTML{
	private $mededeling;

	public function __construct(Mededeling $mededeling){
		$this->mededeling=$mededeling;

	}

	public function view(){

		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('mededeling', $this->mededeling);
		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('prioriteiten', Mededeling::getPrioriteiten());
		$content->assign('datumtijdFormaat', '%d-%m-%Y %H:%M');
		
		// Een standaard vervaltijd verzinnen indien nodig.
		if($this->mededeling->getVervaltijd()===null){
			$standaardVervaltijd=new DateTime(getDateTime());
			$standaardVervaltijd=$standaardVervaltijd->format('Y-m-d 23:59');
			$content->assign('standaardVervaltijd', $standaardVervaltijd);
		}

		$content->display('mededelingen/mededeling.tpl');
	}
}
?>