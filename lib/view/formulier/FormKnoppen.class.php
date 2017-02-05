<?php

/**
 * FormKnoppen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Uitbreidingen van FormulierKnop:
 * 		- SubmitKnop		invoer wordt verzonden
 * 		- ResetKnop			invoer wordt teruggezet naar opgeslagen waarden
 * 		- CancelKnop		invoer wordt genegeerd
 * 		- DeleteKnop		invoer wordt verwijderd
 * 
 */
abstract class FormKnoppen implements FormElement {

	private $knoppen_left = array();
	private $knoppen_right = array();
	public $css_classes = array();

	public function __construct() {
		$this->css_classes[] = 'FormKnoppen';
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
		return get_class($this);
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
			$html .= '<div class="float-left">';

			foreach ($this->knoppen_left as $knop) {
				$html .= $knop->getHtml();
			}
			$html .= '</div>';
		}
		if (!empty($this->knoppen_right)) {
			$html .= '<div class="float-right">';
			foreach ($this->knoppen_right as $knop) {
				$html .= $knop->getHtml();
			}
			$html .= '</div>';
		}
		return $html . '</div>';
	}

	public function view() {
		echo $this->getHtml();
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

class ModalCloseButtons extends FormKnoppen {

	public $close_top;
	public $close_bottom;

	public function __construct() {
		parent::__construct();
		$this->close_bottom = new FormulierKnop(null, 'cancel', 'Sluiten', 'Venster sluiten', null);
		$this->addKnop($this->close_bottom);
	}

}

class FormDefaultKnoppen extends FormKnoppen {

	public $submit;
	public $reset;
	public $cancel;

	public function __construct($cancel_url = null, $reset = true, $icons = true, $labels = true, $cancel_reset = false, $submit_reset = false, $submit_DataTableResponse = false) {
		parent::__construct();

		$this->submit = new SubmitKnop();
		if ($cancel_reset) {
			$this->submit->icon = 'accept';
		}
		if ($submit_reset) {
			$this->submit->action .= ' reset';
		}
		if ($submit_DataTableResponse) {
			$this->submit->action .= ' DataTableResponse';
		}
		$this->addKnop($this->submit);
		if ($reset) {
			$this->reset = new ResetKnop();
			$this->addKnop($this->reset);
		}
		if ($cancel_url !== false) {
			$this->cancel = new CancelKnop($cancel_url);
			if ($cancel_reset) {
				$this->cancel->action .= ' reset';
			}
			$this->addKnop($this->cancel);
		}
		if (!$icons) {
			foreach ($this->getModel() as $knop) {
				$knop->icon = null;
			}
		}
		if (!$labels) {
			foreach ($this->getModel() as $knop) {
				$knop->label = null;
			}
		}
	}

	public function setConfirmAll() {
		foreach ($this->getModel() as $knop) {
			$knop->action .= ' confirm';
		}
	}

}

class FormulierKnop implements FormElement {

	protected $id;
	public $url;
	public $action;
	public $data;
	public $icon;
	public $label;
	public $title;
	public $css_classes = array('FormulierKnop');

	public function __construct($url, $action, $label, $title, $icon) {
		$this->id = uniqid('knop_');
		$this->url = $url;
		$this->action = $action;
		$this->label = $label;
		$this->title = $title;
		$this->icon = $icon;
		$this->css_classes[] = $this->getType();
		$this->css_classes[] = 'btn';
	}

	public function getId() {
		return $this->id;
	}

	public function getModel() {
		return null;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function getHtml() {
		$this->css_classes[] = $this->action;
		$html = '<a id="' . $this->getId() . '"' . ($this->url ? ' href="' . $this->url . '"' : '') . ' class="' . implode(' ', $this->css_classes) . '" title="' . htmlspecialchars($this->title) . '"';
		if (isset($this->data)) {
			$html .= ' data="' . $this->data . '"';
		}
		if (strpos($this->action, 'cancel') !== false) {
			$html .= ' data-dismiss="modal"';
		}
		$html .= '>';
		if ($this->icon) {
			$html .= Icon::getTag($this->icon);
		}
		$html .= $this->label;
		return $html . '</a> ';
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getJavascript() {
		return <<<JS

/* {$this->getId()} */
JS;
	}

}

class SubmitKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'submit', $label = 'Opslaan', $title = 'Invoer opslaan', $icon = 'disk') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

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

class ResetKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'reset', $label = 'Reset', $title = 'Reset naar opgeslagen gegevens', $icon = 'arrow_rotate_anticlockwise') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

class CancelKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'cancel', $label = 'Annuleren', $title = 'Niet opslaan en terugkeren', $icon = 'delete') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

class DeleteKnop extends FormulierKnop {

	public function __construct($url, $action = 'post confirm redirect', $label = 'Verwijderen', $title = 'Definitief verwijderen', $icon = 'cross') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}
