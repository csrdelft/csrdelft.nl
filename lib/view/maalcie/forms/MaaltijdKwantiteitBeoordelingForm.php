<?php
/**
 * MaaltijdKwantiteitBeoordelingForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\SterrenField;

/**
 * MaaltijdKwaliteitBeoordelingForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het invoeren van een beoordeling van een maaltijd met sterren.
 *
 */
class MaaltijdKwantiteitBeoordelingForm extends InlineForm
{
	public function __construct(
		Maaltijd $maaltijd,
		MaaltijdBeoordeling $beoordeling
	) {
		$field = new SterrenField('kwantiteit', $beoordeling->kwantiteit, null, 4);
		$field->hints = [
			'ruim onvoldoende',
			'onvoldoende',
			'voldoende',
			'ruim voldoende',
		];
		$field->click_submit = true;
		$field->readonly =
			$maaltijd->getBeginMoment() <
			date_create_immutable()->modify(
				InstellingUtil::instelling('maaltijden', 'beoordeling_periode')
			);

		parent::__construct(
			$beoordeling,
			'/maaltijden/ketzer/beoordeling/' . $beoordeling->maaltijd_id,
			$field,
			false
		);
		$this->css_classes[] = 'noanim';
	}
}
