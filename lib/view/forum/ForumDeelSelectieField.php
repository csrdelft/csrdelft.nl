<?php


namespace CsrDelft\view\forum;


use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * Class ForumDeelSelectieField
 * @package CsrDelft\view\forum
 * @property ForumCategorie[] $model
 */
class ForumDeelSelectieField extends InputField {
	public $required = true;

	public function getHtml() {
		$html = "<select " . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ">";

		if ($this->getValue() == null) {
			$html .= "<option value=\"\" selected></option>";
		} else {
			$html .= "<option value=\"\"></option>";
		}


		foreach ($this->model as $categorie) {
			$html .= "<optgroup label=\"$categorie->titel\">";

			foreach ($categorie->getForumDelen() as $deel) {
				$isPublic = $deel->isOpenbaar() ? "true" : "false";
				$selected = '';
				if ($deel->forum_id == $this->getValue()) {
					$selected = 'selected';
				}
				$html .= "<option data-is-public=\"$isPublic\" value=\"$deel->forum_id\" $selected>$deel->titel</option>";
			}
			$html .= "</optgroup>";
		}

		$html .= "</select>";
		return $html;
	}
}
