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

		$content->display('mededelingen/mededeling.tpl');
	}
}
?>