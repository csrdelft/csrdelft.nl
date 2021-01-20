<?php

namespace CsrDelft\Component\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BbTextType extends AbstractType
{
	public function getParent()
	{
		return TextareaType::class;
	}
}
