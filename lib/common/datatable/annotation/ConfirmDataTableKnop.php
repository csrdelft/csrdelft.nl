<?php


namespace CsrDelft\common\datatable\annotation;


use CsrDelft\view\datatable\Multiplicity;

/**
 * Class ConfirmDataTableKnop
 * @package CsrDelft\common\datatable\annotation
 * @Annotation
 */
class ConfirmDataTableKnop extends DataTableKnop {
	public function getKnop() {
		return new \CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop(new Multiplicity($this->multiplicity), $this->url, $this->label, $this->title, $this->icon);
	}

}
