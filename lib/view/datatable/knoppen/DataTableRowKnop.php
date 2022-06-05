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
	private $title;
	private $icon;
	private $action;
	private $css;
	private $method;

	public function __construct(
		$action,
		$title,
		$icon,
		$css = '',
		$method = 'post'
	) {
		$this->title = $title;
		$this->icon = $icon;
		$this->action = $action;
		$this->method = $method;
		$this->css = $css;
	}

	public function jsonSerialize()
	{
		return [
			'action' => $this->action,
			'title' => $this->title,
			'icon' => 'ico ' . Icon::get($this->icon),
			'css' => $this->css,
			'method' => $this->method,
		];
	}
}
