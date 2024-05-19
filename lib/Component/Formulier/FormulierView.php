<?php

namespace CsrDelft\Component\Formulier;

use CsrDelft\common\CsrException;

class FormulierView
{
	private $view;
	private $titel;

	public function __construct($view, $titel)
	{
		$this->view = $view;
		$this->titel = $titel;
	}

	public function getView()
	{
		return $this->view;
	}

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBreadcrumbs()
	{
		throw new CsrException('Niet geimplementeerd');
	}

	public function getModel()
	{
		throw new CsrException('Niet geimplementeerd');
	}

	public function toString()
	{
		return $this->view;
	}

	public function __toString(): string
	{
		return $this->view;
	}
}
