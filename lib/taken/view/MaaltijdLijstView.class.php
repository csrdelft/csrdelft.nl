<?php

/**
 * MaaltijdLijstView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van de lijst van aanmeldingen, betaalmogelijkheden en maaltijdgegevens.
 * 
 */
class MaaltijdLijstView extends TemplateView {

	private $_maaltijd;
	private $_aanmeldingen;
	private $_corvee;
	private $_fiscaal;

	public function __construct($maaltijd, $aanmeldingen, $corvee, $fiscaal = false) {
		parent::__construct();
		$this->_maaltijd = $maaltijd;
		$this->_aanmeldingen = $aanmeldingen;
		$this->_corvee = $corvee;
		$this->_fiscaal = $fiscaal;
	}

	public function getTitel() {
		return $this->_maaltijd->getTitel();
	}

	public function view() {
		$this->smarty->assign('maaltijd', $this->_maaltijd);
		$this->smarty->assign('prijs', sprintf('%.2f', $this->_maaltijd->getPrijs()));

		if (!$this->_fiscaal) {
			for ($i = $this->_maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
				$this->_aanmeldingen[] = new MaaltijdAanmelding();
			}
			if (sizeof($this->_aanmeldingen) % 2 === 1) { // altijd even aantal voor lijst
				$this->_aanmeldingen[] = new MaaltijdAanmelding();
			}
			$this->smarty->assign('eterstotaal', $this->_maaltijd->getAantalAanmeldingen() + $this->_maaltijd->getMarge());
			$this->smarty->assign('corveetaken', $this->_corvee);
		}
		$this->smarty->assign('aanmeldingen', $this->_aanmeldingen);

		if ($this->_fiscaal) {
			$this->smarty->display('taken/maaltijd/maaltijd_lijst_fiscaal.tpl');
		} else {
			$this->smarty->display('taken/maaltijd/maaltijd_lijst.tpl');
		}
	}

}

?>