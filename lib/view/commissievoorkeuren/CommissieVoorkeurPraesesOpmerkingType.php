<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 18:16
 */

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CommissieVoorkeurPraesesOpmerkingType extends AbstractType
{
	/**
	 * CommissieVoorkeurOpmerkingForm constructor.
	 * @param VoorkeurOpmerking $model
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('praesesOpmerking', TextareaType::class)
			->add('opslaan', SubmitType::class);
	}
}
