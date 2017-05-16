<?php
/**
 * FotoAlbumZijbalkView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\view\fotoalbum;

use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\LidInstellingenModel;

class FotoAlbumZijbalkView extends FotoAlbumView {

	public function __construct(FotoAlbum $fotoalbum) {
		// als het album alleen subalbums bevat kies een willkeurige daarvan om fotos van te tonen
		if (count($fotoalbum->getFotos()) === 0) {
			$subalbums = $fotoalbum->getSubAlbums();
			$count = count($subalbums);
			if ($count > 0) {
				$idx = rand(0, $count - 1);
				$fotoalbum = $subalbums[$idx];
			}
		}
		parent::__construct($fotoalbum);
	}

	public function view() {
		echo '<div id="zijbalk_fotoalbum">';
		echo '<div class="zijbalk-kopje"><a href="/fotoalbum/' . LichtingenModel::getHuidigeJaargang() . '">Fotoalbum</a></div>';
		echo '<div class="item">';
		echo '<p><a href="' . $this->model->getUrl() . '">' . $this->model->dirname . '</a></p>';
		echo '<div class="fotos">';
		$fotos = $this->model->getFotos();
		$limit = count($fotos);
		if ($limit > LidInstellingenModel::get('zijbalk', 'fotos')) {
			$limit = LidInstellingenModel::get('zijbalk', 'fotos');
		}
		shuffle($fotos);
		for ($i = 0; $i < $limit; $i++) {
			echo '<a href="' . $this->model->getUrl() . '#' . $fotos[$i]->getResizedUrl() . '"><img src="' . $fotos[$i]->getThumbUrl() . '"></a>';
		}
		echo '</div></div></div>';
	}

}
