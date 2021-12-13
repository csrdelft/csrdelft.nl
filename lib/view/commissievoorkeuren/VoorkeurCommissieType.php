<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 19:21
 */

namespace CsrDelft\view\commissievoorkeuren;


use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VoorkeurCommissieType extends AbstractType
{
	/**
	 * AddCommissieFormulier constructor.
	 *
	 * @see VoorkeurCommissie
	 *
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('naam', TextType::class, ['required' => true])
			->add('categorie', EntityType::class, ['class' => VoorkeurCommissieCategorie::class, 'choice_label' => 'naam'])
			->add('zichtbaar', CheckboxType::class)
			->add('opslaan', SubmitType::class)
		;
	}
}
