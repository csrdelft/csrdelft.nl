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

	public function __construct(array $menus, MenuItem $tree_root) {
		parent::__construct($tree_root);
		$this->smarty->assign('menus', $menus);
		$this->smarty->assign('root', $tree_root);
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

class MenuItemFormView extends Formulier {

	private $id;

	public function __construct(MenuItem $item, $actie, $id) {
		parent::__construct($item, 'menu-item-form', $actie);
		$this->id = $id;
		$this->css_classes[] = 'popup PreventUnchanged ReloadPage';

		$fields[] = new RequiredIntField('parent_id', $item->parent_id, 'Parent id');
		$fields[] = new IntField('prioriteit', $item->prioriteit, 'Prioriteit');
		$fields[] = new TextField('tekst', $item->tekst, 'Tekst');
		$fields[] = new TextField('link', $item->link, 'Url');
		$fields[] = new TextField('rechten_bekijken', $item->rechten_bekijken, 'Rechten');
		$fields[] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields[] = new SubmitResetCancel();
		$this->addFields($fields);
	}

	public function getAction() {
		return '/menubeheer/' . $this->actie . '/' . $this->id;
	}

	public function view() {
		echo '<div id="popup-content"><h1>Menu-item ' . $this->actie . '</h1>';
		echo parent::view();
		echo '</div>';
	}

}
