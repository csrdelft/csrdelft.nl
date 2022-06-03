<?php


namespace CsrDelft\view\cms;


use CsrDelft\Component\Form\Type\BbTextType;
use CsrDelft\Component\Form\Type\DateDisplayType;
use CsrDelft\entity\CmsPagina;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsPaginaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('laatstGewijzigd', DateDisplayType::class)
            ->add('titel', TextType::class)
            ->add('rechtenBekijken', TextType::class, ['disabled' => !$options['rechten_wijzigen']])
            ->add('rechtenBewerken', TextType::class, ['disabled' => !$options['rechten_wijzigen']]);

        if ($options['rechten_wijzigen']) {
            $builder
                ->add('inlineHtml', ChoiceType::class, [
                    'expanded' => true,
                    'choices' => [
                        'Direct <html>' => true,
                        '[html] tussen [/html]' => false,
                    ],
                    'help' => 'Geen [html] nodig en zelf regeleindes plaatsen met [rn] of <br />'
                ]);
        }

        $builder
            ->add('inhoud', BbTextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsPagina::class,
            'rechten_wijzigen' => false,
        ]);
    }
}
