<?php


namespace CsrDelft\common\datatable\annotation;

/**
 * Class DataTableRowKnop
 * @package CsrDelft\common\datatable\annotation
 * @Annotation
 */
class DataTableRowKnop {
	public $title;
	public $icon;
	public $action;
	public $css = '';
	public $method = 'post';

	public function getKnop() {
		return new \CsrDelft\view\datatable\knoppen\DataTableRowKnop($this->action, $this->title, $this->icon, $this->css, $this->method);
	}
}
