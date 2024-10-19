<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FotoBBView
 * @package CsrDelft\view\fotoalbum
 */
class FotoBBView implements ToResponse, View
{
	public function __construct(
		private readonly Foto $model,
		private $groot = false,
		private $responsive = false
	) {
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
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
			!$this->groot &&
			InstellingUtil::lid_instelling('forum', 'fotoWeergave') == 'boven bericht'
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
			($this->groot &&
				InstellingUtil::lid_instelling('forum', 'fotoWeergave') != 'nee') ||
			InstellingUtil::lid_instelling('forum', 'fotoWeergave') == 'in bericht'
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
