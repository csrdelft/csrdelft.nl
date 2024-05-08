<?php

namespace CsrDelft\view;

/**
 * Je moet zelf valideren of de output niet per ongeluk gekke dingen bevat.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class PlainView implements View, ToResponse
{
	use ToHtmlResponse;
	private $body;

	public function __construct($body)
	{
		$this->body = $body;
	}

	public function __toString()
	{
		return $this->body;
	}

	public function getTitel(): string
	{
		return '';
	}

	public function getBreadcrumbs(): string
	{
		return '';
	}

	public function getModel(): null
	{
		return null;
	}
}
