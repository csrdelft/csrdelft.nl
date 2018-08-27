<?php

namespace CsrDelft\view\renderer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/08/2018
 */
interface Renderer {
	public function assign($field, $value);
	public function render();
	public function display();
}
