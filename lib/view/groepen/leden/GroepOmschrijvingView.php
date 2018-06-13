<?php
/**
 * GroepOmschrijvingView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\elementen\FormElement;

class GroepOmschrijvingView implements FormElement {

	protected $groep;
	protected $javascript;

	public function __construct(AbstractGroep $groep) {
		$this->groep = $groep;
		$this->javascript = '';
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getJavascript() {
		return $this->javascript;
	}

	public function getHtml() {
		$this->javascript .= <<<JS

$('#groep-omschrijving-{$this->groep->id}').hide().slideDown(600);
JS;
		echo '<div id="groep-omschrijving-' . $this->groep->id . '">';
		echo CsrBB::parse($this->groep->omschrijving);
		echo $this->getScriptTag();
		echo '</div>';
	}

	public function view() {
		echo $this->getHtml();
	}

	protected function getScriptTag() {
		return <<<JS
<script type="text/javascript">
$(document).ready(function () {
	{$this->getJavascript()}
});
</script>
JS;
	}

    public function __toString()
    {
        ob_start();
        $this->view();
        return ob_get_clean();
    }
}
