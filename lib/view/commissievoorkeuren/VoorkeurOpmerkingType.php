<?php

namespace CsrDelft\view\commissievoorkeuren;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VoorkeurOpmerkingType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('lidOpmerking', TextType::class);
	}
}
