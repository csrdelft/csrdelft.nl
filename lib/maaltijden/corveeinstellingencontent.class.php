<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/corveeinstellingen.class.php
# -------------------------------------------------------------------
# Instellingen bekijken en aanpassen
# -------------------------------------------------------------------


require_once ('maaltijden/maaltrack.class.php');

class CorveeinstellingenContent extends SimpleHTML {
	private $corveeinstellingen;

	function __construct($corveeinstellingen) {
		$this->corveeinstellingen=$corveeinstellingen;
	}

	function getTitel(){ return 'Maaltijdketzer - corveeinstellingen'; }

	function view(){
		$loginlid=LoginLid::instance();

		//de html template in elkaar draaien en weergeven
		$pagina=new Smarty_csr();


		//arrays toewijzen en weergeven
		$pagina->assign('instellingen', $this->corveeinstellingen);
		$pagina->assign('melding', $this->getMelding());
		$pagina->display('maaltijdketzer/corveeinstellingen.tpl');
	}
}

?>
