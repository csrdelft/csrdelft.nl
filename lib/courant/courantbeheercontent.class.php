<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# courant/class.courantbeheer.php
# -------------------------------------------------------------------

class CourantBeheercontent extends SmartyTemplateView {

	private $_edit = 0; //bericht wat bewerkt moet worden.
	private $formulier = array();

	public function __construct(Courant $courant) {
		parent::__construct($courant, 'C.S.R.-courant');

		//standaardwaarden.
		$this->formulier['ID'] = 0;
		$this->formulier['categorie'] = 'overig';
		$this->formulier['titel'] = '';
		$this->formulier['bericht'] = '';
	}

	public function getBreadcrumbs() {
		$breadcrumbs = '<a href="/actueel/courant" title="Courant"><img src="' . CSR_PICS . '/knopjes/email-16.png" class="module-icon"></a>';
		if (isset($this->formulier['titel']) AND ! empty($this->formulier['titel'])) {
			$breadcrumbs .= ' » ' . $this->formulier['titel'];
		}
		return $breadcrumbs;
	}

	function edit($iBerichtID) {
		$this->_edit = (int) $iBerichtID;

		//voor bewerken waarden eventueel overschrijven met waarden uit de database
		if ($this->_edit != 0) {
			//nog dingen ophalen.
			$this->formulier = $this->model->getBericht($this->_edit);
		}
	}

	function view() {
		//als er gepost is de meuk uit post halen.
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($_POST['titel'])) {
				$this->formulier['titel'] = trim($_POST['titel']);
			}
			if (isset($_POST['categorie'])) {
				$this->formulier['categorie'] = trim($_POST['categorie']);
			}
			if (isset($_POST['bericht'])) {
				$this->formulier['bericht'] = trim($_POST['bericht']);
			}
		} else {
			//als we een keer op voorbeeld hebben gedrukt en er is nog niets gesubmit geven
			//we dit weer.
			if (isset($_SESSION['compose_snapshot'])) {
				$this->formulier['bericht'] = htmlspecialchars($_SESSION['compose_snapshot']);
			}
		}
		$this->smarty->assign('courant', $this->model);
		$this->smarty->assign('form', $this->formulier);
		$this->smarty->display('courant/courantbeheer.tpl');
	}

}
