<?php

/**
 * MenuBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class MenuBeheerView extends SmartyTemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root, 'Menubeheer');
	}

	public function view() {
		$this->smarty->assign('root', $this->model);
		$this->smarty->assign('menus', MenuModel::instance()->getBeheerMenusVoorLid());
		$this->smarty->display('MVC/menu/beheer/menu_tree.tpl');
	}

}

class MenuItemView extends SmartyTemplateView {

	public function __construct(MenuItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('MVC/menu/beheer/menu_item.tpl');
	}

}

class MenuItemForm extends ModalForm {

	public function __construct(MenuItem $item, $actie, $id) {
		parent::__construct($item, 'menu-item-form', '/menubeheer/' . $actie . '/' . $id);
		if ($id === 'favoriet') {
			$this->titel = 'Favoriet ' . $actie;
		} else {
			$this->titel = 'Menu-item ' . $actie;
		}
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}
		$this->css_classes[] = 'ReloadPage';

		$fields['pid'] = new RequiredIntField('parent_id', $item->parent_id, 'Parent ID', 0);
		$fields['pid']->title = 'ID van het menu-item waar dit item onder valt';
		if (!LoginModel::mag('P_ADMIN')) {
			$fields['pid']->hidden = true;
		}

		$fields['prio'] = new IntField('prioriteit', $item->prioriteit, 'Volgorde');
		$fields['prio']->title = 'Volgorde van menu-items';

		$fields[] = new TextField('tekst', $item->tekst, 'Korte aanduiding', 50);

		$fields['url'] = new TextField('link', $item->link, 'Link');
		$fields['url']->title = 'URL als er op het menu-item geklikt wordt';

		$fields['r'] = new RechtenField('rechten_bekijken', $item->rechten_bekijken, 'Lees-rechten');
		$fields['r']->title = 'Wie mag dit menu-item zien';
		if (!LoginModel::mag('P_ADMIN')) {
			$fields['r']->hidden = true;
		}

		$fields['z'] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields['z']->title = 'Wel of niet tonen';

		$fields[] = new FormButtons();
		$this->addFields($fields);
	}

}
