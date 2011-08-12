<?php
class MededelingContent extends SimpleHTML{
	private $mededeling;
	private $prullenbak;

	public function __construct(Mededeling $mededeling, $prullenbak=false){
		$this->mededeling=$mededeling;
		$this->prullenbak = $prullenbak;
	}

	public function view(){

		$content=new Smarty_csr();
		define( 'NIEUWS_ROOT', '/actueel/mededelingen/');

		$content->assign('mededeling', $this->mededeling);
		$content->assign('prullenbak', $this->prullenbak);
		$content->assign('melding', $this->getMelding());
		$content->assign('nieuws_root', NIEUWS_ROOT);
		$content->assign('prioriteiten', Mededeling::getPrioriteiten());
		$content->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		$content->assign('aantalTopMostBlock', MededelingenContent::aantalTopMostBlock);
		
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