<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FotoBBView
 * @package CsrDelft\view\fotoalbum
 */
class FotoBBView implements ToResponse, View
{
	private $groot;
	private $responsive;
	private $model;

	public function __construct(Foto $foto, $groot = false, $responsive = false)
	{
		$this->model = $foto;
		$this->groot = $groot;
		$this->responsive = $responsive;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function getHtml()
	{
		$html = '<a href="' . $this->model->getAlbumUrl();
		if ($this->groot) {
			$html .= '?fullscreen';
		}
		$html .= '#' . $this->model->getFullUrl() . '" class="';
		if ($this->responsive) {
			$html .= 'responsive';
		}
		if (
			!$this->groot and
			lid_instelling('forum', 'fotoWeergave') == 'boven bericht'
		) {
			$html .=
				' hoverIntent"><div class="hoverIntentContent"><span class="bb-img-loading" data-src="' .
				$this->model->getResizedUrl() .
				'"></span></div>';
		} else {
			$html .= '">';
		}
		$html .= '<div class="bb-img-loading" data-src="';
		if (
			$this->groot and lid_instelling('forum', 'fotoWeergave') != 'nee' or
			lid_instelling('forum', 'fotoWeergave') == 'in bericht'
		) {
			$html .= $this->model->getResizedUrl();
		} else {
			$html .= $this->model->getThumbUrl();
		}
		$html .= '"></div>';
		return $html;
	}

	public function toResponse(): Response
	{
		return new Response($this->getHtml());
	}

	public function getTitel()
	{
		return '';
	}

	public function getBreadcrumbs()
	{
		return '';
	}

	public function getModel()
	{
		return null;
	}
}
