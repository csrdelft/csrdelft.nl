<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 18:16
 */

namespace CsrDelft\view\commissievoorkeuren;


use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CommissieVoorkeurPraesesOpmerkingType extends AbstractType {

	/**
	 * CommissieVoorkeurOpmerkingForm constructor.
	 * @param VoorkeurOpmerking $model
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('praesesOpmerking', TextareaType::class)
			->add('opslaan', SubmitType::class)
			;
	}
}
