<?php

namespace CsrDelft\view\menubeheer;

use CsrDelft\entity\MenuItem;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLegacyTextField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\required\RequiredUrlField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class MenuItemForm extends ModalForm {

	public function __construct(MenuItem $item, $actie, $id) {
		parent::__construct($item, '/menubeheer/' . $actie . '/' . $id);
		if ($id == 'favoriet') {
			$this->titel = 'Favoriet ' . $actie;
		} else {
			$this->titel = 'Menu-item ' . $actie;
		}
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields = [];
		$fields['pid'] = new DoctrineEntityField('parent', $item->parent, 'Parent', MenuItem::class, '');
		$fields['pid']->title = 'ID van het menu-item waar dit item onder valt';
		if (!LoginService::mag(P_ADMIN) OR $id == 'favoriet') {
			$fields['pid']->readonly = true;
			$fields['pid']->hidden = true;
		}

		$fields['v'] = new IntField('volgorde', $item->volgorde, 'Volgorde');
		$fields['v']->title = 'Volgorde van menu-items';

		$fields[] = new RequiredTextField('tekst', $item->tekst, 'Korte aanduiding', 50);

		$fields['url'] = new RequiredUrlField('link', $item->link, 'Link');
		$fields['url']->title = 'URL als er op het menu-item geklikt wordt';

		$fields['r'] = new RechtenField('rechten_bekijken', $item->rechten_bekijken, 'Lees-rechten');
		$fields['r']->title = 'Wie mag dit menu-item zien';
		if (!LoginService::mag(P_ADMIN) OR $id == 'favoriet') {
			$fields['r']->readonly = true;
			$fields['r']->hidden = true;
		}

		$fields['z'] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields['z']->title = 'Wel of niet tonen';

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
