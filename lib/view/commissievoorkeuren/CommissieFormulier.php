<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 18-3-18
 * Time: 20:10
 */

namespace CsrDelft\view\commissievoorkeuren;


use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieCategorieRepository;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class CommissieFormulier extends Formulier {

	/**
	 * CommissieFormulier constructor.
	 * @param mixed $model
	 */
	public function __construct($model) {
		parent::__construct($model, "/commissievoorkeuren/overzicht/" . $model->id);
		$opties = [];
		foreach (ContainerFacade::getContainer()->get(VoorkeurCommissieCategorieRepository::class)->findAll() as $cat) {
			$opties[$cat->id] = $cat->naam;
		}
		$this->addFields([new HtmlComment("<p>Hier kunnen instellingen voor de commissie worden aangepast. Onderaan de pagina staan de leden die een voorkeur voor deze commissie hebben opgegeven.</p>")]);
		$this->addFields([new CheckboxField('zichtbaar', $this->model->zichtbaar, "Tonen aan leden")]);
		$this->addFields([new SelectField("categorie_id", $this->model->categorie_id, "Categorie", $opties)]);

		$this->formKnoppen->addKnop(new SubmitKnop());
	}
}
