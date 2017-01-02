<?php

/**
 * DataTableKnop.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class DataTableKnop extends FormulierKnop {

	public $dataTableId;
	private $multiplicity;

	public function __construct($multiplicity, $url, $action, $label, $title, $class = 'text') {
		parent::__construct($url, $action . ' DataTableResponse', $label, $title, null);
		$this->multiplicity = $multiplicity;
		$this->css_classes[] = 'DTTT_button';
		$this->css_classes[] = 'DTTT_button_' . $class;
	}

	public function getUpdateToolbar() {
		return "$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity})).blur().toggleClass('DTTT_disabled', !(aantal {$this->multiplicity}));";
	}

	public function getHtml() {
		return str_replace('<a ', '<a data-tableid="' . $this->dataTableId . '" ', parent::getHtml());
	}

}
