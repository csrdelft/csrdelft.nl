<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant/class.courantbeheer.php
# -------------------------------------------------------------------

class CourantBeheercontent extends SmartyTemplateView {

	private $_edit = 0; //bericht wat bewerkt moet worden.

	public function __construct(&$courant) {
		parent::__construct($courant, 'C.S.R.-courant');
	}

	function edit($iBerichtID) {
		$this->_edit = (int) $iBerichtID;
	}

	function view() {
		$formulier = array();

		//standaardwaarden.
		$formulier['ID'] = 0;
		$formulier['categorie'] = 'overig';
		$formulier['titel'] = '';
		$formulier['bericht'] = '';

		//voor bewerken waarden eventueel overschrijven met waarden uit de database
		if ($this->_edit != 0) {
			//nog dingen ophalen.
			$formulier = $this->model->getBericht($this->_edit);
		}

		//als er gepost is de meuk uit post halen.
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($_POST['titel'])) {
				$formulier['titel'] = trim($_POST['titel']);
			}
			if (isset($_POST['categorie'])) {
				$formulier['categorie'] = trim($_POST['categorie']);
			}
			if (isset($_POST['bericht'])) {
				$formulier['bericht'] = trim($_POST['bericht']);
			}
		} else {
			//als we een keer op voorbeeld hebben gedrukt en er is nog niets gesubmit geven
			//we dit weer.
			if (isset($_SESSION['compose_snapshot'])) {
				$formulier['bericht'] = htmlspecialchars($_SESSION['compose_snapshot']);
			}
		}
		$this->smarty->assign('courant', $this->model);
		$this->smarty->assign('form', $formulier);
		$this->smarty->display('courant/courantbeheer.tpl');
	}

}
