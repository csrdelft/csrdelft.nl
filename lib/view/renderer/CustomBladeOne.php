<?php


namespace CsrDelft\view\renderer;


use eftec\bladeone\BladeOne;

class CustomBladeOne extends BladeOne {
	protected function compilecsrf() {
		return $this->phpTag."printCsrfField();?>";
	}
}