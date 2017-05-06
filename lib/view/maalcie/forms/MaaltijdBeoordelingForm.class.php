<?php
namespace CsrDelft\view\maalcie\forms;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\model\InstellingenModel;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\SterrenField;

/**
 * MaaltijdBeoordelingForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het invoeren van een beoordeling van een maaltijd met sterren.
 * 
 */
class MaaltijdKwantiteitBeoordelingForm extends InlineForm {

	public function __construct(Maaltijd $m, MaaltijdBeoordeling $b) {

		$field = new SterrenField('kwantiteit', $b->kwantiteit, null, 4);
		$field->hints = array('ruim onvoldoende', 'onvoldoende', 'voldoende', 'ruim voldoende');
		$field->click_submit = true;
		$field->readonly = $m->getBeginMoment() < strtotime(InstellingenModel::get('maaltijden', 'beoordeling_periode'));

		parent::__construct($b, maalcieUrl . '/beoordeling/' . $b->maaltijd_id, $field, false);
		$this->css_classes[] = 'noanim';
	}

}

class MaaltijdKwaliteitBeoordelingForm extends InlineForm {

	public function __construct(Maaltijd $m, MaaltijdBeoordeling $b) {

		$field = new SterrenField('kwaliteit', $b->kwaliteit, null, 4);
		$field->hints = array('ruim onvoldoende', 'onvoldoende', 'voldoende', 'ruim voldoende');
		$field->click_submit = true;
		$field->readonly = $m->getBeginMoment() < strtotime(InstellingenModel::get('maaltijden', 'beoordeling_periode'));

		parent::__construct($b, maalcieUrl . '/beoordeling/' . $b->maaltijd_id, $field, false);
		$this->css_classes[] = 'noanim';
	}

}
