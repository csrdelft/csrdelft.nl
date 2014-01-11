<?php


require_once 'lid/saldi.class.php';
require_once 'taken/view/MaalCieSaldiView.class.php';
require_once 'taken/view/forms/BoekjaarSluitenFormView.class.php';

/**
 * MaalCieSaldiController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaalCieSaldiController extends \AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_MAAL_SALDI'
			);
		}
		else {
			$this->acl = array(
				'upload' => 'P_MAAL_SALDI',
				'sluitboekjaar' => 'P_MAAL_SALDI'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction();
	}
	
	public function beheer() {
		$this->content = new MaalCieSaldiView();
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function upload() {
		$this->beheer();
		$melding_level = \Saldi::putMaalcieCsv();
		$this->content->setMelding($melding_level[0], $melding_level[1]);
	}
	
	public function sluitboekjaar() {
		$form = new BoekjaarSluitenFormView(date('Y-m-d', strtotime('-1 year')), date('Y-m-d')); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$errors_aantal = MaaltijdenModel::archiveerOudeMaaltijden(strtotime($values['begindatum']), strtotime($values['einddatum']));
			$this->content = new MaalCieSaldiView(true);
			if (sizeof($errors_aantal[0]) === 0) {
				$this->content->setMelding('Boekjaar succesvol gesloten: '. $errors_aantal[1] .' maaltijden naar het archief verplaatst.', 1);
			}
		}
		else {
			$this->content = $form;
		}
	}
}

?>