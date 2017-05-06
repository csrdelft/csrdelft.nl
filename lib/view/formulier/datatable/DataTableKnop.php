<?php
/**
 * DataTableKnop.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\formulier\datatable;

use CsrDelft\Icon;
use JsonSerializable;

class DataTableKnop implements JsonSerializable {

	protected $multiplicity;
	protected $tableId;
	protected $label;
	protected $url;
	protected $icon;
	protected $id;
	protected $extend;
	protected $buttons;
	protected $title;

	public function __construct($multiplicity, $tableId, $url, $action, $label, $title, $icon = '', $extend = 'default') {
		$this->icon = $icon;
		$this->label = $label;
		$this->title = $title;
		$this->url = $url;
		$this->multiplicity = $multiplicity;
		$this->tableId = $tableId;
		$this->extend = $extend;
		$this->buttons = array();
	}

	public function addKnop(DataTableKnop $knop) {
		$this->buttons[] = $knop;
	}

	public function jsonSerialize() {
		return array(
			'text' => $this->label,
			'titleAttr' => $this->title,
			'multiplicity' => $this->multiplicity,
			'extend' => $this->extend,
			'href' => $this->url,
			'className' => $this->icon ? 'dt-button-ico dt-ico-' . Icon::get($this->icon) : '',
			'dataTableId' => $this->tableId,
			'autoClose' => true,
			'buttons' => $this->buttons
		);
	}
}