<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\view\SmartyTemplateView;

class FotoBBView extends SmartyTemplateView {

	private $groot;
	private $responsive;

	public function __construct(
		Foto $foto,
		$groot = false,
		$responsive = false
	) {
		parent::__construct($foto);
		$this->groot = $groot;
		$this->responsive = $responsive;
	}

	public function getHtml() {
		$html = '<a href="' . $this->model->getAlbumUrl();
		if ($this->groot) {
			$html .= '?fullscreen';
		}
		$html .= '#' . $this->model->getResizedUrl() . '" class="';
		if ($this->responsive) {
			$html .= 'responsive';
		}
		if (!$this->groot AND lid_instelling('forum', 'fotoWeergave') == 'boven bericht') {
			$html .= ' hoverIntent"><div class="hoverIntentContent"><div class="bb-img-loading" src="' . $this->model->getResizedUrl() . '"></div></div>';
		} else {
			$html .= '">';
		}
		$html .= '<div class="bb-img-loading" src="';
		if (($this->groot AND lid_instelling('forum', 'fotoWeergave') != 'nee') OR lid_instelling('forum', 'fotoWeergave') == 'in bericht') {
			$html .= $this->model->getResizedUrl();
		} else {
			$html .= $this->model->getThumbUrl();
		}
		$html .= '"></div></a>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

}
