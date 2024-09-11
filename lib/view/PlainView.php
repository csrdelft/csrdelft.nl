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

	public function __construct(private $body)
	{
	}

	public function __toString(): string
	{
		return (string) $this->body;
	}

	public function getTitel()
	{
		return '';
	}

	public function getBreadcrumbs()
	{
		return '';
	}

	public function getModel()
	{
		return null;
	}
}
