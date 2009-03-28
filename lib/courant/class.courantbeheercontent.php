<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant/class.courantbeheer.php
# -------------------------------------------------------------------

class CourantBeheercontent extends SimpleHTML{

	private $courant;				//db object voor de courant

	private $_edit=0;				//bericht wat bewerkt moet worden.


	function CourantBeheercontent(&$courant){
		$this->courant=$courant;
	}

	function edit($iBerichtID){ $this->_edit=(int)$iBerichtID; }
	function getTitel(){ return 'C.S.R.-courant'; }

	function view(){

		$formulier=array();

		//standaardwaarden.
		$formulier['ID']=0;
		$formulier['categorie']='overig';
		$formulier['titel']='';
		$formulier['bericht']='';

		//voor bewerken waarden eventueel overschrijven met waarden uit de database
		if($this->_edit!=0){
			//nog dingen ophalen.
			$formulier=$this->courant->getBericht($this->_edit);
		}

		//als er gepost is de meuk uit post halen.
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(isset($_POST['titel'])){ $formulier['titel']=htmlspecialchars(trim($_POST['titel'])); }
			if(isset($_POST['categorie'])){ $formulier['categorie']=htmlspecialchars(trim($_POST['categorie'])); }
			if(isset($_POST['bericht'])){ $formulier['bericht']=htmlspecialchars(trim($_POST['bericht'])); }
		}

			$content=new Smarty_csr();

		$content->assign('courant', $this->courant);
		$content->assign('form', $formulier);
		$content->assign('melding', $this->getMelding());

		$content->display('courant/courantbeheer.tpl');

	}
}//einde classe

?>
