<?php

namespace CsrDelft\view\forum;

use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\LegacyTextField;

/**
 * Met een zoekterm op de zoekpagina terecht komen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/03/2019
 */
class ForumSnelZoekenForm extends Formulier {
	public function __construct() {
		parent::__construct(new ForumZoeken(), '/forum/zoeken');
		$this->showMelding = false;
		$this->css_classes[] = 'flex-grow-1';

		$fields = [];
		$fields['z'] = new LegacyTextField('zoekterm', null, null);
		$fields['z']->placeholder = 'Zoeken in forum';
		$fields['z']->enter_submit = true;

		$this->addFields($fields);
	}
}
