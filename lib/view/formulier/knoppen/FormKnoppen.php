<?php

namespace CsrDelft\view\formulier\knoppen;

use CsrDelft\view\formulier\FormElement;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Uitbreidingen van FormulierKnop:
 *    - SubmitKnop    invoer wordt verzonden
 *    - ResetKnop      invoer wordt teruggezet naar opgeslagen waarden
 *    - CancelKnop    invoer wordt genegeerd
 *    - DeleteKnop    invoer wordt verwijderd
 *
 */
abstract class FormKnoppen implements FormElement {

	private $knoppen_left = array();
	private $knoppen_right = array();
	public $css_classes = array();

	public function __construct() {
		$this->css_classes[] = 'FormKnoppen';
		$this->css_classes[] = 'clearfix';
		$this->css_classes[] = $this->getType();
	}

	public function getModel() {
		return array_merge($this->knoppen_left, $this->knoppen_right);
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

	public function addKnop(FormulierKnop $knop, $left = false, $prepend = false) {
		if ($left) {
			if ($prepend) {
				array_unshift($this->knoppen_left, $knop);
			} else {
				$this->knoppen_left[] = $knop;
			}
		} else {
			if ($prepend) {
				array_unshift($this->knoppen_right, $knop);
			} else {
				$this->knoppen_right[] = $knop;
			}
		}
	}

	public function getHtml() {
		$html = '<div class="' . implode(' ', $this->css_classes) . '">';
		if (!empty($this->knoppen_left)) {
			$html .= '<div class="float-start">';

			foreach ($this->knoppen_left as $knop) {
				$html .= $knop->getHtml();
			}
			$html .= '</div>';
		}
		if (!empty($this->knoppen_right)) {
			$html .= '<div class="float-end">';
			foreach ($this->knoppen_right as $knop) {
				$html .= $knop->getHtml();
			}
			$html .= '</div>';
		}
		return $html . '</div>';
	}

	public function __toString() {
		return $this->getHtml();
	}

	public function getJavascript() {
		$js = <<<JS

/* {$this->getTitel()} */
JS;
		foreach ($this->getModel() as $knop) {
			$js .= $knop->getJavascript();
		}
		return $js;
	}

}
