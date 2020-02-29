<?php


namespace CsrDelft\common\datatable\annotation;


use CsrDelft\view\datatable\Multiplicity;

/**
 * Class DataTableKnop
 * @package CsrDelft\common\datatable\annotation
 * @Annotation
 */
class DataTableKnop {
	/**
	 * @var string
	 * @Enum({"", "== 0", "== 1", "== 2", ">= 1"})
	 */
	public $multiplicity;
	public $tableId;
	public $label;
	public $url;
	public $icon;
	public $id;
	public $extend = "default";
	public $buttons;
	public $title;

	public function getKnop() {
		return new \CsrDelft\view\datatable\knoppen\DataTableKnop(new Multiplicity($this->multiplicity), $this->url, $this->label, $this->title, $this->icon, $this->extend);
	}
}
