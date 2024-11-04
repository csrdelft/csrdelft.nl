<?php

namespace CsrDelft\Component\Formulier;

use CsrDelft\common\CsrException;

class FormulierView implements \Stringable
{
	public function __construct(private $view, private $titel)
	{
	}

	public function getModel(): never
	{
		throw new CsrException('Niet geimplementeerd');
	}

	public function __toString(): string
	{
		return (string) $this->view;
	}
}
