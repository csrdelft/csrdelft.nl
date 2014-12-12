<?php

require_once 'MVC/model/BijbelroosterModel.class.php';

/**
 * BijbelroosterView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BijbelroosterView implements View {

	/**
	 * Geen array want we itereren slechts 1x in de view
	 * @var PDOStatement
	 */
	protected $rooster;

	public function __construct($rooster) {
		$this->rooster = $rooster;
	}

	public function getModel() {
		return $this->rooster;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Bijbelrooster';
	}

	public function getHtml() {
		if (!$this->rooster) {
			return 'Geen rooster aanwezig';
		}
		$html = '<div class="bijbelrooster">';
		$nu = strtotime(date('Y-m-d'));
		foreach ($this->rooster as $stukje) {
			$html .= '<span';
			if (strtotime($stukje->dag) < $nu) {
				$html .= ' class="lichtgrijs"';
			}
			$html .= '>' . date('Y-m-d', strtotime($stukje->dag)) . ': </span>' . $stukje->getLink(true) . '<br/>';
		}
		$html .= '</div>';
		return $html;
	}

	public function view() {
		echo '<h1>Bijbelrooster</h1><p>Hier vindt u het bijbelrooster der C.S.R.. Uw favoriete bijbelvertaling kunt u instellen bij uw <a href="/instellingen">instellingen</a>.</p>';
		echo $this->getHtml();
	}

}

class BijbelroosterBBView extends BijbelroosterView {

	public function __construct($dagen) {
		$dagen = (int) max((int) $dagen, 2);
		$van = strtotime('-' . $dagen . ' days');
		$tot = strtotime('+' . $dagen . ' days');
		$rooster = BijbelroosterModel::instance()->getBijbelroosterTussen($van, $tot);
		parent::__construct($rooster);
	}

	public function getHtml() {
		$html = '<div class="bb-block mededeling-grotebalk">';
		$html .= parent::getHtml();
		$html .= '<div class="titel" style="float:right;position:relative;bottom:1.5em;right:10px"><a href="/bijbelrooster">Bijbelleesrooster</a></div></div>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

}
