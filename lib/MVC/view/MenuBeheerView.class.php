<?php

/**
 * MenuBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class MenuBeheerView extends TemplateView {

	public function __construct(MenuItem $tree_root, array $menus) {
		parent::__construct($tree_root);
		if ($tree_root->tekst === '') {
			$this->model = false;
		}
		$this->smarty->assign('root', $this->model);
		$this->smarty->assign('menus', $menus);
	}

	public function getTitel() {
		if ($this->model) {
			return 'Beheer ' . $this->model->tekst . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		$this->smarty->display('MVC/menu/beheer/menu_tree.tpl');
	}

}

class MenuItemView extends TemplateView {

	public function __construct(MenuItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('MVC/menu/beheer/menu_item.tpl');
	}

}

class MenuItemFormView extends PopupForm {

	public function __construct(MenuItem $item, $actie) {
		parent::__construct($item, 'menu-item-form', $actie);
		$this->css_classes[] = 'ReloadPage';
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}
		$this->generateFields();
		$fields['zichtbaar'] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields['zichtbaar']->title = 'Wel of niet tonen';
		$this->addFields($fields);
	}

	public function getAction() {
		return '/menubeheer/' . $this->action . '/' . $this->model->item_id;
	}

	public function getTitel() {
		return 'Menu-item ' . $this->action;
	}

}
