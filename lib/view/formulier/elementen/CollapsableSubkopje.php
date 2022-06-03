<?php

namespace CsrDelft\view\formulier\elementen;
/**
 * CollapsableSubkopje.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Je moet zelf de DIV sluiten!
 */
class CollapsableSubkopje extends Subkopje {
	private $collapsed;

	public function __construct($titel, $collapsed = false) {
		parent::__construct($titel);
		$this->collapsed = $collapsed;
	}

	public function getHtml() {
		$className = $this->collapsed ? "collapse" : "collapse show";
		$expanded = $this->collapsed ? "false" : "true";
		$collapseId = uniqid("collapsable_");
		$content = parent::getHtml();

		return <<<HTML
<a data-bs-toggle="collapse" href="#{$collapseId}" role="button" aria-expanded="{$expanded}" aria-controls="{$collapseId}">{$content}</a>
<div id="{$collapseId}" class="{$className}">
HTML;
	}

	public function __toString() {
		return $this->getHtml();
	}

}
