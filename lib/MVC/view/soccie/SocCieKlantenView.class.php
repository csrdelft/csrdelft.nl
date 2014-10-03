<?php

/**
 * SocCieKlantenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bevat o.a. view voor tonen van SocCie-saldo.
 * 
 */
class SocCieKlantSaldoView implements View {

	/**
	 * Klant om saldo van te tonen
	 * @var SocCieKlant
	 */
	public $klant;

	public function __construct(SocCieKlant $klant) {
		$this->klant = $klant;
	}

	public function getModel() {
		return $this->klant;
	}

	public function getTitel() {
		return 'SocCie saldo';
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('saldo', $this->getModel()->getSaldoFloat());
		$smarty->display('MVC/soccie/klanten/saldo.tpl');
	}

}
