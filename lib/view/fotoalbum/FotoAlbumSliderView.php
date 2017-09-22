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
	public $interval = 5;
	public $random = true;

	public function getHtml() {
		$this->smarty->assign('sliderId', uniqid('slider'));
		$this->smarty->assign('album', $this->model);
		$this->smarty->assign('itemsJson', json_encode($this->model->getAlbumArray()));
		$this->smarty->assign('height', $this->height);
		$this->smarty->assign('interval', $this->interval);
		$this->smarty->assign('random', $this->random);
		return $this->smarty->fetch('fotoalbum/slider.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}