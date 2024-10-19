<?php

namespace CsrDelft\view\datatable\knoppen;

use CsrDelft\view\Icon;
use JsonSerializable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 03/08/2019
 */
class DataTableRowKnop implements JsonSerializable
{
	public function __construct(
		private $action,
		private $title,
		private $icon,
		private $css = '',
		private $method = 'post'
	) {
	}

	public function jsonSerialize(): array
	{
		return [
			'action' => $this->action,
			'title' => $this->title,
			'icon' => 'fas fa-' . Icon::get($this->icon),
			'css' => $this->css,
			'method' => $this->method,
		];
	}
}
