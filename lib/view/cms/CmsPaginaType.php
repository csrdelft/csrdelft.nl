<?php


namespace CsrDelft\view\cms;


use CsrDelft\Component\Form\Type\BbTextType;
use CsrDelft\entity\CmsPagina;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsPaginaType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('laatstGewijzigd', DateTimeType::class, ['disabled' => true])
			->add('titel', TextType::class)
			->add('rechtenBekijken', TextType::class, ['disabled' => !$options['rechten_wijzigen']])
			->add('rechtenBewerken', TextType::class, ['disabled' => !$options['rechten_wijzigen']]);

		if ($options['rechten_wijzigen']) {
			$builder
				->add('inlineHtml', CheckboxType::class, [
					'help' => 'Geen [html] nodig en zelf regeleindes plaatsen met [rn] of <br />'
				]);
		}

		$builder
			->add('inhoud', BbTextType::class)
			->add('save', SubmitType::class);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CmsPagina::class,
			'rechten_wijzigen' => false,
		]);

	}


}
