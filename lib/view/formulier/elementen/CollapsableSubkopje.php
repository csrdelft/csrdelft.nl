<?php

namespace CsrDelft\view\formulier\elementen;
/**
 * CollapsableSubkopje.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Je moet zelf de DIV sluiten!
 */
class CollapsableSubkopje extends Subkopje {

	private $id;
	public $collapsed;
	public $single;
	public $hover_click;
	private $expand;
	private $collapse;

	public function __construct($id, $titel, $collapsed = false, $single = false, $hover_click = false, $animate = true) {
		parent::__construct($titel);
		$this->id = $id;
		$this->collapsed = $collapsed;
		$this->single = $single;
		$this->hover_click = $hover_click;
		if ($animate) {
			$this->expand = 'slideDown(200)';
			$this->collapse = 'slideUp(200)';
		} else {
			$this->expand = 'show()';
			$this->collapse = 'hide()';
		}
	}

	public function getJavascript() {
		$js = parent::getJavascript() . <<<JS

$('#toggle_kopje_{$this->id}').click(function() {
	if ($('#expand_kopje_{$this->id}').is(':visible')) {
JS;
		// niet inklappen?
		if (!$this->hover_click) {
			$js .= <<<JS

		$('#expand_kopje_{$this->id}').{$this->collapse};
		$(this).removeClass('toggle-group-expanded');
JS;
		}
		$js .= <<<JS
	} else {
JS;
		// de rest inklappen?
		if ($this->single) {
			$js .= <<<JS

$(this).siblings('.expanded-submenu').{$this->collapse};
$(this).siblings('.toggle-group').removeClass('toggle-group-expanded');
JS;
		}
		// uitklappen:
		$js .= <<<JS

		$('#expand_kopje_{$this->id}').{$this->expand};
		$(this).addClass('toggle-group-expanded');
	}
});
JS;
		// uitklappen bij hover?
		if ($this->hover_click) {
			$js .= <<<JS

try {
	$('#toggle_kopje_{$this->id}').hoverIntent(function() {
		$(this).trigger('click');
	});
} catch(err) {
	console.log(err);
	// Missing js file
}
JS;
		}
		return $js;
	}

	public function getHtml() {
		return '<div id="toggle_kopje_' . $this->id . '" class="toggle-group ' . ($this->collapsed ? '' : 'toggle-group-expanded') . '">'
			. parent::getHtml() .
			'</div><div id="expand_kopje_' . $this->id . '" class="expanded-submenu" ' . ($this->collapsed ? 'style="display:none;"' : '') . '>';
	}

	public function view() {
		echo $this->getHtml();
	}

}
