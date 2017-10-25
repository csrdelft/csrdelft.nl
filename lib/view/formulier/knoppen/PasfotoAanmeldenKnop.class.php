<?php

namespace CsrDelft\view\formulier\knoppen;

use CsrDelft\model\security\LoginModel;

/**
 * PasfotoAanmeldenKnop.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class PasfotoAanmeldenKnop extends SubmitKnop {

	public function getHtml() {
		if (($i = array_search('btn', $this->css_classes)) !== false) {
			unset($this->css_classes[$i]);
		}
		$this->css_classes[] = 'lidLink';
		$this->label = null;
		$this->icon = false;
		$img = '<img class="pasfoto float-none" src="/plaetjes/groepen/aanmelden.jpg" onmouseout="this.src=\'/plaetjes/groepen/aanmelden.jpg\'" onmouseover="this.src=\'/plaetjes/' . LoginModel::getProfiel()->getPasfotoPath() . '\'" title="Klik om u aan te melden" style="cursor:pointer;">';
		return str_replace('</a>', $img . '</a>', parent::getHtml());
	}

}
