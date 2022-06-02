<?php


namespace CsrDelft\view\formulier\knoppen;


use Twig\Environment;

class TemplateFormKnoppen extends FormKnoppen
{

	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var string
	 */
	private $template;
	/**
	 * @var array
	 */
	private $options;

	public function __construct(Environment $twig, $template, $options = [])
	{
		parent::__construct();
		$this->twig = $twig;
		$this->template = $template;
		$this->options = $options;
	}

	public function getHtml()
	{
		return $this->twig->render($this->template, $this->options);
	}

}
