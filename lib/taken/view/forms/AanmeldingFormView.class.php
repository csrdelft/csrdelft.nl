<?php



/**
 * AanmeldingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te verwijderen maaltijd-aanmelding.
 * 
 */
class AanmeldingFormView extends TemplateView {

	private $_form;
	private $_mid;
	private $_nieuw;

	public function __construct($mid, $nieuw, $uid = null, $gasten = 0) {
		parent::__construct();
		$this->_mid = $mid;
		$this->_nieuw = $nieuw;

		$formFields[] = new LidField('voor_lid', $uid, 'Naam of lidnummer', 'leden');
		if ($nieuw) {
			$formFields[] = new IntField('aantal_gasten', $gasten, 'Aantal gasten', 200, 0);
		}

		$this->_form = new Formulier('taken-aanmelding-form', $GLOBALS['taken_module'] . '/ander' . ($nieuw ? 'aanmelden' : 'afmelden') . '/' . $mid, $formFields);
	}

	public function getTitel() {
		if ($this->_nieuw) {
			return 'Aanmelding toevoegen/aanpassen';
		}
		return 'Aanmelding verwijderen (inclusief gasten)';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';
		$this->assign('form', $this->_form);
		$this->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_mid) || $this->_mid <= 0) {
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>