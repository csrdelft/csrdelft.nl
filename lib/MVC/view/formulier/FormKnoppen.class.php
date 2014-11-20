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

	protected $knoppen = array();
	public $css_classes = array('clear-left');

	public function getModel() {
		return $this->knoppen;
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

	public function addKnop(FormulierKnop $knop, $prepend = false) {
		if ($prepend) {
			array_unshift($this->knoppen, $knop);
		} else {
			$this->knoppen[] = $knop;
		}
	}

	public function view() {
		echo '<div class="' . $this->getType() . ' ' . implode(' ', $this->css_classes) . '"><div class="float-left">';
		foreach ($this->knoppen as $knop) {
			if ($knop->float_left) {
				$knop->view();
			}
		}
		echo '</div><div class="float-right">';
		foreach ($this->knoppen as $knop) {
			if (!$knop->float_left) {
				$knop->view();
			}
		}
		echo '</div></div>';
	}

	public function getJavascript() {
		$js = '';
		foreach ($this->knoppen as $knop) {
			$js .= $knop->getJavascript();
		}
		return $js;
	}

}

class FormDefaultKnoppen extends FormKnoppen {

	public function __construct($cancel_url = null, $reset = true, $icons = true, $label = true, $reset_cancel = false) {
		$this->knoppen['submit'] = new SubmitKnop();
		if ($reset) {
			$this->knoppen['reset'] = new ResetKnop();
		}
		$this->knoppen['cancel'] = new CancelKnop($cancel_url);
		if ($reset_cancel) {
			$this->knoppen['cancel']->action .= ' reset';
			$this->knoppen['cancel']->title = 'Niet bevestigen en terugkeren';
			$this->knoppen['submit']->label = 'Bevestigen';
			$this->knoppen['submit']->title = 'Invoer bevestigen';
			$this->knoppen['submit']->icon = '/famfamfam/accept.png';
		}
		if (!$icons) {
			foreach ($this->knoppen as $knop) {
				$knop->icon = null;
			}
		}
		if (!$label) {
			foreach ($this->knoppen as $knop) {
				$knop->label = null;
			}
		}
	}

	public function confirmAll() {
		foreach ($this->knoppen as $knop) {
			$knop->action .= ' confirm';
		}
	}

}

class FormulierKnop implements FormElement {

	protected $id;
	public $url;
	public $action;
	public $icon;
	public $label;
	public $title;
	public $float_left;
	public $css_classes;

	public function __construct($url, $action, $label, $title, $icon, $float_left = false) {
		$this->id = 'knop' . crc32($url . $action);
		$this->url = $url;
		$this->action = $action;
		$this->label = $label;
		$this->title = $title;
		$this->icon = $icon;
		$this->float_left = $float_left;
		$this->css_classes = array();
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

	public function view() {
		echo '<a id="' . $this->getId() . '"' . ($this->url ? ' href="' . $this->url . '"' : '') . ' class="btn ' . $this->action . ' ' . implode(' ', $this->css_classes) . '" title="' . htmlspecialchars($this->title) . '">';
		if ($this->icon) {
			echo '<img src="' . CSR_PICS . $this->icon . '" class="icon" width="16" height="16" /> ';
		}
		echo $this->label . '</a> ';
	}

	public function getJavascript() {
		if (strpos($this->action, 'submit') !== false AND isset($this->url)) {
			return "$('#{$this->getId()}').unbind('click.action').bind('click.action', form_submit_url);";
		}
		return '';
	}

}

class SubmitKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'submit', $label = 'Opslaan', $title = 'Invoer opslaan', $icon = '/famfamfam/disk.png') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

class ResetKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'reset', $label = 'Reset', $title = 'Reset naar opgeslagen gegevens', $icon = '/famfamfam/arrow_rotate_anticlockwise.png') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

class CancelKnop extends FormulierKnop {

	public function __construct($url = null, $action = 'cancel', $label = 'Annuleren', $title = 'Niet opslaan en terugkeren', $icon = '/famfamfam/delete.png') {
		parent::__construct($url, $action, $label, $title, $icon);
	}

}

class DeleteKnop extends FormulierKnop {

	public function __construct($url, $action = 'post confirm ReloadPage', $label = 'Verwijderen', $title = 'Definitief verwijderen', $icon = '/famfamfam/cross.png') {
		parent::__construct($url, $action, $label, $title, $icon, true);
	}

}
