<?php
/**
 * ModalForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\formulier;

/**
 * Form as modal content.
 */
class ModalForm extends Formulier {

	public function view() {
		$this->css_classes[] = 'ModalForm';
		echo '<div id="modal" class="modal"><div class="modal-dialog modal-content modal-lg" tabindex="-1" style="display: block;">';

		$titel = $this->getTitel();
		if (!empty($titel)) {
			echo '<div class="modal-header">
        <h5 class="modal-title">' . $titel . '</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>';
		}
		if ($this->showMelding) {
			echo getMelding();
		}
		echo $this->getFormTag();

		if (isset($this->error)) {
			echo '<span class="error">' . $this->error . '</span>';
		}
		echo '<div class="modal-body">';
		//debugprint($this->getError()); //DEBUG
		foreach ($this->getFields() as $field) {
			$field->view();
		}
		echo '</form>';
		printDebug();
		echo '</div></div>';
		echo $this->getScriptTag();
		echo '</div>';
	}

}