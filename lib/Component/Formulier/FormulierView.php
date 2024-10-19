<?php

namespace CsrDelft\Component\Formulier;

use Stringable;
use CsrDelft\common\CsrException;

class FormulierView implements Stringable
{
	public function __construct(private $view, private $titel)
	{
	}

	public function getView()
	{
		return $this->view;
	}

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBreadcrumbs(): never
	{
		throw new CsrException('Niet geimplementeerd');
	}

	public function getModel(): never
	{
		throw new CsrException('Niet geimplementeerd');
	}

	public function toString()
	{
		return $this->view;
	}

	public function __toString(): string
	{
		return (string) $this->view;
	}
}
