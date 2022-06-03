<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurVoorkeur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommissieVoorkeurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('voorkeur', ChoiceType::class, [
            'choices' => array('nee' => 1, 'ja' => 2, 'mischien' => 3),
            'label_format' => 'commissie', //$entity->getCommissieNaam(),
        ]);
        $builder->add('commissieNaam', HiddenType::class, ['disabled' => true]);
        $builder->add('categorieNaam', HiddenType::class, ['disabled' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VoorkeurVoorkeur::class,
        ]);
    }
}
