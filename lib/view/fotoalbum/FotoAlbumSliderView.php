<?php
/**
 * FotoAlbumSliderView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\fotoalbum;

class FotoAlbumSliderView extends FotoAlbumZijbalkView {

	public $height = 360;
	public $interval = 5000;
	public $random = true;

	public function getHtml() {
		$fotos = $this->model->getAlbumArray();

		if ($this->random) {
			shuffle($fotos);
		}
		$this->smarty->assign('height', $this->height);
		$this->smarty->assign('interval', $this->interval);
		$this->smarty->assign('fotos', $fotos);
		return $this->smarty->fetch('fotoalbum/slider.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}
