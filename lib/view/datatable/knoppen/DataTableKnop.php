<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\datatable\Multiplicity;
use CsrDelft\view\Icon;
use JsonSerializable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */
class DataTableKnop implements JsonSerializable {
	protected $multiplicity;
	protected $tableId;
	protected $label;
	protected $url;
	protected $icon;
	protected $id;
	protected $extend = "default";
	protected $buttons;
	protected $title;

	public function __construct(Multiplicity $multiplicity, $url, $label, $title, $icon = '', $extend = 'default') {
		$this->icon = $icon;
		$this->label = $label;
		$this->title = $title;
		$this->url = $url;
		$this->multiplicity = $multiplicity;
		$this->extend = $extend;
		$this->buttons = array();
	}

	public function jsonSerialize() {
		return [
			'text' => $this->label,
			'titleAttr' => $this->title,
			'multiplicity' => $this->multiplicity->getChoice(),
			'extend' => $this->extend,
			'href' => $this->url,
			'className' => $this->getIconClass(),
			'dataTableId' => $this->tableId,
			'autoClose' => true,
		];
	}

	public function setDataTableId($dataTableId) {
		$this->tableId = $dataTableId;

		foreach ($this->buttons as $button) {
			$button->tableId = $dataTableId;
		}
	}

	/**
	 * @return string
	 */
	protected function getIconClass(): string {
		return $this->icon ? 'dt-button-ico dt-ico-' . Icon::get($this->icon) : '';
	}
}
