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
		parent::__construct($maaltijd, $maaltijd->getTitel());
		$this->fiscaal = $fiscaal;

		$this->addStylesheet('jquery-ui.min.css', '/layout/js/jquery/themes/ui-lightness/');
		$this->addScript('jquery/jquery-2.1.0.min.js');
		$this->addScript('jquery/jquery-ui-1.10.4.custom.min.js');
		$this->addScript('jquery/plugins/jquery.hoverIntent-r7.min.js');
		$this->addScript('csrdelft.js');
		$this->addScript('taken.js');

		if (!$fiscaal) {
			$this->addStylesheet('maaltijdlijst.css');

			for ($i = $maaltijd->getMarge(); $i > 0; $i--) { // ruimte voor marge eters
				$aanmeldingen[] = new MaaltijdAanmelding();
			}
			$aantal = count($aanmeldingen);
			$tabel1 = array_slice($aanmeldingen, 0, intval($aantal / 2), true);
			$tabel2 = array_diff_key($aanmeldingen, $tabel1);

			$this->smarty->assign('aanmeldingen', array($tabel1, $tabel2));
			$this->smarty->assign('eterstotaal', $maaltijd->getAantalAanmeldingen() + $maaltijd->getMarge());
			$this->smarty->assign('corveetaken', $corvee);
			
		}
		else {
			$this->smarty->assign('aanmeldingen', $aanmedlingen);
		}
		$this->smarty->assign('maaltijd', $maaltijd);
		$this->smarty->assign('prijs', sprintf('%.2f', $maaltijd->getPrijs()));
	}

	public function view() {
		if ($this->fiscaal) {
			$this->smarty->display('taken/maaltijd/maaltijd_lijst_fiscaal.tpl');
		} else {
			$this->smarty->display('taken/maaltijd/maaltijd_lijst.tpl');
		}
	}

}
