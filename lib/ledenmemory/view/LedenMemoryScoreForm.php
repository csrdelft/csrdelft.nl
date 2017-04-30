<?php
/**
 * LedenMemoryScoreForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/04/2017
 */
class LedenMemoryScoreForm extends Formulier {

	public function __construct(LedenMemoryScore $score) {
		parent::__construct($score, '/leden/memoryscore');

		$fields[] = new RequiredIntField('tijd', $score->tijd, null, 1);
		$fields[] = new RequiredIntField('beurten', $score->beurten, null, 1);
		$fields[] = new RequiredIntField('goed', $score->goed, null, 1);
		$fields[] = new TextField('groep', $score->groep, null);
		$fields[] = new RequiredIntField('eerlijk', $score->eerlijk, null, 0, 1);

		$this->addFields($fields);
	}

}