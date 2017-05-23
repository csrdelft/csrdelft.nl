<?php
namespace CsrDelft\view\bijbelrooster;

use CsrDelft\model\BijbelroosterModel;
use CsrDelft\view\bijbelrooster\BijbelroosterView;

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
