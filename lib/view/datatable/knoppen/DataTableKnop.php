<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\datatable\Multiplicity;
use CsrDelft\view\Icon;
use JsonSerializable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */
class DataTableKnop implements JsonSerializable
{
	protected $multiplicity;
	protected $tableId;
	protected $id;
	protected $buttons;

	public function __construct(
		Multiplicity $multiplicity,
		protected $url,
		protected $label,
		protected $title,
		protected $icon = '',
		protected $extend = 'default'
	) {
		$this->multiplicity = $multiplicity;
		$this->buttons = [];
	}

	public function jsonSerialize(): array
	{
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

	public function setDataTableId($dataTableId)
	{
		$this->tableId = $dataTableId;

		foreach ($this->buttons as $button) {
			$button->tableId = $dataTableId;
		}
	}

	/**
	 * @return string
	 */
	protected function getIconClass(): string
	{
		return $this->icon ? 'dt-button-ico fa-' . Icon::get($this->icon) : '';
	}
}
