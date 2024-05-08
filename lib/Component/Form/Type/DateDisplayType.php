<?php

namespace CsrDelft\Component\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateDisplayType extends AbstractType
{
	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		$view->vars['relative'] = $options['relative'];
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'relative' => true,
		]);

		$resolver->setAllowedTypes('relative', 'bool');
	}
}
