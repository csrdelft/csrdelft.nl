<?php


namespace CsrDelft\view\datatable;


use CsrDelft\common\datatable\DataTableAnnotationReader;

class AnnotationDataTable extends DataTable {
	public function init() {
		$annotationReader = new DataTableAnnotationReader($this->orm);

		$properties = $annotationReader->getProperties();

		foreach ($properties as $field => $property) {
			$this->addColumn($property->name, null, null, $property->type ? new CellRender($property->type) : null);
			if ($property->hidden) {
				$this->hideColumn($field);
			}
		}

		foreach ($annotationReader->getKnoppen() as $knop) {
			$this->addKnop($knop);
		}

		foreach ($annotationReader->getRowKnoppen() as $rowKnop) {
			$this->addRowKnop($rowKnop);
		}

		$config = $annotationReader->getConfig();

		if ($config->order) {
			$this->setOrder($config->order);
		}
	}
}
