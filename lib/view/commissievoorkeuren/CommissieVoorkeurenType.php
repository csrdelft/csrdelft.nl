<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommissieVoorkeurenType extends AbstractType
{
	/**
	 * @return void
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('lidOpmerking', TextareaType::class)
			->add('opslaan', SubmitType::class);
	}

	/**
	 * @return void
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => VoorkeurOpmerking::class,
		]);
	}
}
