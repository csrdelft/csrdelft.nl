<?php

/**
 * MaaltijdLijstView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van de lijst van aanmeldingen, betaalmogelijkheden en maaltijdgegevens.
 * 
 */
class MaaltijdLijstView extends CompressedLayout {

	private $fiscaal;

	public function __construct(Maaltijd $maaltijd, $aanmeldingen, $corvee, $fiscaal = false) {
		parent::__construct('layout', $this, $maaltijd->getTitel());
		$this->fiscaal = $fiscaal;

		$this->addCompressedResources('maalcielijst');
		$smarty = CsrSmarty::instance();

		if (!$this->fiscaal) {
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
		$smarty->assign('prijs', sprintf('%.2f', $maaltijd->getPrijsFloat()));
	}

	public function getBreadcrumbs() {
		return null;
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
