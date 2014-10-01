<?php

/**
 * MaaltijdLijstView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van de lijst van aanmeldingen, betaalmogelijkheden en maaltijdgegevens.
 * 
 */
class MaaltijdLijstView extends HtmlPage {

	private $fiscaal;

	public function __construct(Maaltijd $maaltijd, $aanmeldingen, $corvee, $fiscaal = false) {
		parent::__construct($this, $maaltijd->getTitel());
		$this->fiscaal = $fiscaal;

		$jquery = '/layout/js/jquery/';
		$this->addStylesheet($jquery . 'jquery-ui');
		$this->addScript($jquery . 'jquery');
		$this->addScript($jquery . 'jquery-ui');
		$this->addScript($jquery . 'plugins/jquery.hoverIntent');
		$this->addScript('/layout/js/csrdelft');
		$this->addScript('/layout/js/maalcie');

		$smarty = CsrSmarty::instance();

		if (!$this->fiscaal) {
			$this->addStylesheet('/layout/css/ubb');
			$this->addStylesheet('/layout/css/maaltijdlijst');

			for ($i = $maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
				$aanmeldingen[] = new MaaltijdAanmelding();
			}
			$totaal = $maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge();
			$tabel1 = array_slice($aanmeldingen, 0, intval($totaal / 2), true);
			$tabel2 = array_diff_key($aanmeldingen, $tabel1);

			$smarty->assign('aanmeldingen', array($tabel1, $tabel2));
			$smarty->assign('eterstotaal', $totaal);
			$smarty->assign('corveetaken', $corvee);
		} else {
			$smarty->assign('aanmeldingen', $aanmeldingen);
		}
		$smarty->assign('maaltijd', $maaltijd);
		$smarty->assign('prijs', sprintf('%.2f', $maaltijd->getPrijs()));
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());

		if ($this->fiscaal) {
			$smarty->display('maalcie/maaltijd/maaltijd_lijst_fiscaal.tpl');
		} else {
			$smarty->display('maalcie/maaltijd/maaltijd_lijst.tpl');
		}
	}

}
