<?php

namespace CsrDelft\view\commissievoorkeuren;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CommissieVoorkeurenType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('voorkeuren', CollectionType::class, [
				'entry_type' => CommissieVoorkeurType::class,
			])
			->add('opmerking', TextareaType::class)
			->add('opslaan', SubmitType::class);
	}
}
