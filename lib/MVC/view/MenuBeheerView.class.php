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

	/**
	 * List of all menus
	 * @var array
	 */
	private $menus;
	/**
	 * Root of the menu tree
	 * @var MenuItem
	 */
	private $tree_root;

	public function __construct(MenuModel $model, $menu_naam = '') {
		parent::__construct($model);
		$this->menus = $model->getAlleMenus();
		if ($menu_naam !== '') {
			$this->tree_root = $model->getMenuTree($menu_naam, true);
		} else {
			$this->tree_root = false;
		}
	}

	public function getTitel() {
		if ($this->tree_root) {
			return 'Beheer ' . $this->tree_root->tekst . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		$this->smarty->assign('menus', $this->menus);
		$this->smarty->assign('root', $this->tree_root);
		$this->smarty->display('MVC/menu/beheer/menu_page.tpl');
	}

}

class MenuItemView extends TemplateView {

	private $actie;

	public function __construct(MenuItem $item, $actie) {
		parent::__construct($item);
		$this->actie = $actie;
	}

	public function view() {
		switch ($this->actie) {

			case 'toevoegen':
			case 'bewerken':
				$this->smarty->assign('item', $this->model);
				$this->smarty->display('MVC/menu/beheer/menu_item.tpl');
				break;

			case 'verwijderen':
				echo '<li id="menu-item-' . $this->model->item_id . '" class="remove"></li>';
				break;
		}
	}

}

class MenuItemFormView extends Formulier {

	private $actie;

	public function __construct(MenuItem $item, $actie, $id) {
		parent::__construct($item, 'menu-item-form', '/menubeheer/' . $actie . '/' . $id);
		$this->actie = $actie;
		$this->css_classes[] = 'popup PreventUnchanged';

		$fields[] = new RequiredIntField('parent_id', $item->parent_id, 'Parent id');
		$fields[] = new IntField('prioriteit', $item->prioriteit, 'Prioriteit');
		$fields[] = new TextField('tekst', $item->tekst, 'Tekst');
		$fields[] = new TextField('link', $item->link, 'Url');
		$fields[] = new TextField('rechten_bekijken', $item->rechten_bekijken, 'Rechten');
		$fields[] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields[] = new SubmitResetCancel();
		$this->addFields($fields);
	}

	public function view() {
		echo '<div id="popup-content"><h1>Menu-item ' . $this->actie . '</h1>';
		echo parent::view();
		echo '</div>';
	}

}
