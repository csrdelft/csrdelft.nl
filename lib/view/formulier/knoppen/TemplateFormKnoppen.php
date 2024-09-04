<?php

namespace CsrDelft\view\formulier\knoppen;

use Twig\Environment;

class TemplateFormKnoppen extends FormKnoppen
{
	/**
	 * @param string $template
	 * @param mixed[] $options
	 */
	public function __construct(
		private readonly Environment $twig,
		private $template,
		private $options = []
	) {
		parent::__construct();
	}

	public function getHtml()
	{
		return $this->twig->render($this->template, $this->options);
	}
}
