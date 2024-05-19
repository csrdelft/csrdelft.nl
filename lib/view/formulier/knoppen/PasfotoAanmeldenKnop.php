<?php

namespace CsrDelft\view\formulier\knoppen;

use CsrDelft\service\security\LoginService;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class PasfotoAanmeldenKnop extends SubmitKnop
{
	public function getHtml(): string
	{
		if (($i = array_search('btn btn-primary', $this->css_classes)) !== false) {
			unset($this->css_classes[$i]);
		}
		$this->css_classes[] = 'lidLink';
		$this->label = null;
		$this->icon = false;
		$img =
			'<img class="pasfoto float-none" src="/plaetjes/groepen/aanmelden.jpg" onmouseout="this.src=\'/plaetjes/groepen/aanmelden.jpg\'" onmouseover="this.src=\'' .
			LoginService::getProfiel()->getPasfotoPath() .
			'\'" title="Klik om u aan te melden" style="cursor:pointer;">';
		return str_replace('</a>', $img . '</a>', parent::getHtml());
	}
}
