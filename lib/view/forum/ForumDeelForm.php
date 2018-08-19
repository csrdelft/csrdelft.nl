<?php

namespace CsrDelft\view\forum;

use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class ForumDeelForm extends ModalForm {

	public function __construct(ForumDeel $deel) {
		parent::__construct($deel, '/forum/beheren/' . $deel->forum_id);
		$this->titel = 'Deelforum beheren';
		$this->css_classes[] = 'ReloadPage';
		$this->css_classes[] = 'PreventUnchanged';

		$lijst = array();
		foreach (ForumModel::instance()->prefetch() as $categorie) {
			$lijst[$categorie->categorie_id] = $categorie->titel;
		}

		$fields = [];
		$fields[] = new SelectField('categorie_id', $deel->categorie_id, 'Categorie', $lijst);
		$fields[] = new RequiredTextField('titel', $deel->titel, 'Titel');
		$fields[] = new TextareaField('omschrijving', $deel->omschrijving, 'Omschrijving');
		$fields[] = new RechtenField('rechten_lezen', $deel->rechten_lezen, 'Lees-rechten');
		$fields[] = new RechtenField('rechten_posten', $deel->rechten_posten, 'Post-rechten');
		$fields[] = new RechtenField('rechten_modereren', $deel->rechten_modereren, 'Mod-rechten');
		$fields[] = new IntField('volgorde', $deel->volgorde, 'Volgorde');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();

		$delete = new DeleteKnop('/forum/opheffen/' . $deel->forum_id);
		$this->formKnoppen->addKnop($delete, true);
	}

}
