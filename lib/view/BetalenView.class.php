<?php

/**
 * BetalenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BetalenView extends SmartyTemplateView {

	public function view() {
		$this->smarty->display('betalen/react_example.tpl');
		echo '<script type="text/babel" src="/layout/jsx/react_example.js"></script>';
		
		
	}

}

class FactuurForm extends ModalForm {
	
}

class FactuurItemForm extends ModalForm {
	
}

class KlantForm extends ModalForm {
	
}

class ProductCategorieForm extends ModalForm {
	
}

class ProductPrijsLijstForm extends ModalForm {
	
}

class ProductPrijsForm extends ModalForm {
	
}

class ProductForm extends ModalForm {
	
}

class StreepLijstProductForm extends ModalForm {
	
}

class StreepLijstForm extends ModalForm {
	
}

class TransactieForm extends ModalForm {
	
}
