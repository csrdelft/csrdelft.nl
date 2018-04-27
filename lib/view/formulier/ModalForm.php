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
		echo '<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1" style="display: block;">';
		parent::view();
		printDebug();
		echo '</div>';
	}

}