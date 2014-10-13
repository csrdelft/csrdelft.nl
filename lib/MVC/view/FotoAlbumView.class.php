<?php

/**
 * FotoAlbumView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De views van het fotoalbum.
 */
class FotoAlbumView extends SmartyTemplateView {

	public function __construct(FotoAlbum $fotoalbum) {
		parent::__construct($fotoalbum);
	}

	function getTitel() {
		return ucfirst($this->model->dirname);
	}

	function view() {
		echo getMelding();
		$this->smarty->assign('album', $this->model);
		$this->smarty->display('MVC/fotoalbum/album.tpl');
	}

	public static function getBreadcrumbs(FotoAlbum $album, $dropdown = false, $self = false) {
		$breadcrumbs = '<div class="breadcrumbs">';
		$mappen = explode('/', $album->getSubDir());
		$subdir = 'fotoalbum/';
		$first = true;
		foreach ($mappen as $albumnaam) {
			if ($first) {
				$first = false;
				$breadcrumbs .= '<a href="/fotoalbum">Fotoalbum</a>';
			} elseif ($albumnaam === '') {
				// trailing slash: allerlaatste
				break;
			} else {
				if ($albumnaam === $album->dirname) {
					// laatste
					if ($dropdown) {
						$breadcrumbs .= ' » ' . FotoAlbumView::getDropDown(PICS_PATH . $subdir, $albumnaam);
						break;
					} elseif (!$self) {
						// alleen parent folders tonen
						break;
					}
				}
				$subdir .= $albumnaam . '/';
				$breadcrumbs .= ' » <a href="/' . $subdir . '">' . ucfirst($albumnaam) . '</a>';
			}
		}
		return $breadcrumbs . '</div>';
	}

	public static function getDropDown($subdir, $albumnaam) {
		$parent = FotoAlbumModel::getFotoAlbum($subdir);
		if (!$parent) {
			return '';
		}
		$albums = $parent->getSubAlbums();
		$dropdown = '<select onchange="location.href=this.value;">';
		foreach ($albums as $album) {
			$dropdown .= '<option value="' . $album->getUrl() . '"';
			if ($album->path === $subdir . $albumnaam . '/') {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . $album->dirname . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

}

class FotoAlbumToevoegenForm extends ModalForm {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, get_class(), '/fotoalbum/toevoegen/' . $album->getSubDir());
		$this->titel = 'Fotoalbum toevoegen in: ' . $album->dirname;
		$this->css_classes[] = 'ReloadPage';
		$fields[] = new RequiredFileNameField('subalbum', null, 'Naam');
		$fields[] = new FormButtons('/fotoalbum', true, true, false);
		$this->addFields($fields);
	}

}

class PosterUploadForm extends Formulier {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, get_class(), '/fotoalbum/uploaden/' . $album->getSubDir());
		$this->titel = 'Poster toevoegen in: ' . basename(dirname($album->getSubDir()));
		$fields[] = new HtmlComment('Alleen jpeg afbeeldingen.<br/><br/>');
		$fields[] = new RequiredFileNameField('posternaam', null, 'Posternaam', 50, 5);
		$fields[] = new RequiredImageField('afbeelding', null, null, array('image/jpeg'));
		$fields[] = new FormButtons('/fotoalbum', true, true, false);
		$fields[] = new HtmlComment('<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>');
		$this->addFields($fields);
	}

	public function view() {
		echo FotoAlbumView::getBreadcrumbs($this->model, false, true);
		parent::view();
	}

}

class FotosDropzone extends DropzoneForm {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album, get_class(), '/fotoalbum/uploaden/' . $album->getSubDir(), new ImageField('afbeelding', null, null, array('image/jpeg'), false));
		$this->titel = 'Fotos toevoegen aan album: ' . $album->dirname;
	}

	public function view() {
		echo FotoAlbumView::getBreadcrumbs($this->model, false, true);
		echo '<div class="float-right"><a class="knop" onclick="showExisting_afbeeldingDropzoneUploader();$(this).remove();"><img src="http://plaetjes.csrdelft.nl/famfamfam/photos.png" width="16" height="16" alt="photos" class="icon"> Toon bestaande foto\'s in dit album</a></div>';
		echo parent::view();
		echo '<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>';
	}

}

class FotoUbbView extends SmartyTemplateView {

	private $groot;

	public function __construct(Foto $foto, $groot = false) {
		parent::__construct($foto);
		$this->groot = $groot;
	}

	public function getHTML() {
		$html = '<a href="' . $this->model->getURL() . '" title="Klik voor origineel formaat"';
		if (!$this->groot AND LidInstellingen::get('forum', 'fotoWeergave') == 'boven bericht') {
			$html .= ' class="hoverIntent"><div class="hoverIntentContent"><div class="ubb_img_loading" src="' . $this->model->getResizedURL() . '"></div></div';
		}
		$html .= '><div class="ubb_img_loading" src="';
		if (($this->groot AND LidInstellingen::get('forum', 'fotoWeergave') != 'nee') OR LidInstellingen::get('forum', 'fotoWeergave') == 'in bericht') {
			$html .= $this->model->getResizedURL();
		} else {
			$html .= $this->model->getThumbURL();
		}
		$html .= '"></div></a>';
		return $html;
	}

	public function view() {
		echo $this->getHTML();
	}

}

class FotoAlbumZijbalkView extends SmartyTemplateView {

	public function __construct(FotoAlbum $album) {
		parent::__construct($album);
	}

	public function view() {
		$url = $this->model->getUrl();
		echo '<div id="zijbalk_fotoalbum">';
		echo '<h1><a href="/actueel/fotoalbum/">Laatste fotoalbum</a></h1>';
		echo '<div class="item">';
		echo '<a href="' . $url . '">' . $this->model->dirname . '</a>';
		echo '<div class="fotos">';
		$fotos = $this->model->getFotos();
		$limit = sizeof($fotos);
		if ($limit > LidInstellingen::get('zijbalk', 'fotos')) {
			$limit = LidInstellingen::get('zijbalk', 'fotos');
		}
		for ($i = 0; $i < $limit; $i++) {
			$foto = $fotos[$i];
			if ($foto instanceof Foto) {
				echo '<a href="' . $url . '#' . direncode($foto->filename) . '">';
				echo '<img src="' . $foto->getThumbURL() . '">';
				echo '</a>' . "\n";
			}
		}
		echo '</div>'; // class="fotos"
		echo '</div>'; // class="item"
		echo '</div>'; // id="zijbalk_fotoalbum"
	}

}

class FotoAlbumUbbView extends SmartyTemplateView {

	private $compact = false; //compact or expanded tag.
	private $rows = 2;  //number of rows
	private $per_row = 7;  //images per row
	private $big = array(); //array with index of the ones to enlarge
	private $picsize = 75;  //size of an image
	private $rowmargin = 2; //margin between the images

	public function __construct(FotoAlbum $album) {
		parent::__construct($album);
	}

	public function view() {
		echo $this->getHTML();
	}

	public function makeCompact() {
		$this->compact = true;
	}

	public function setRows($rows) {
		$this->rows = $rows;
	}

	/**
	 * One integer index or array of integer indexes of images to enlarge.
	 * possible 'macro' enlargements for up to 8 rows:
	 * - a (diagonals),
	 * - b (diagonals),
	 * - c (odd/even)
	 * 
	 * @param string $index
	 */
	public function setBig($index) {
		if (in_array($index, array('a', 'b', 'c'))) {
			switch ($index) {
				case 'a':
					$this->big = array(0, 9, 18, 28, 37, 46);
					break;
				case 'b':
					$this->big = array(0, 4, 15, 19, 28, 32, 43, 47);
					break;
				case 'c':
					$this->big = array(0, 16, 4, 28, 44, 32);
					break;
			}
			return;
		}
		if (count(explode(',', $index)) > 1) {
			//explode on ',' and convert tot int.
			$this->big = array_map('intval', explode(',', $index));
		} else {
			$this->big = array((int) $index);
		}
	}

	/**
	 * Build a grid with Foto-objects.
	 *
	 * The index is saved together with the object for correct reference
	 * in case the image is moved one left or one up in the grid at borders.
	 */
	private function getGrid() {
		$fotos = $this->model->getFotos();
		$grid = array_fill(0, $this->rows, array_fill(0, $this->per_row, null));
		// put big images on grid.
		if (count($this->big) > 0 && $this->rows > 1) {
			foreach ($this->big as $bigindex) {
				$row = floor($bigindex / $this->per_row);
				$col = ($bigindex % $this->per_row);
				// remove images that will cause wrap around.
				if ($col + 1 >= $this->per_row) {
					continue;
				}
				if ($row + 1 >= $this->rows) {
					continue;
				}
				// remove images that will cause overlap with a big image one row up.
				if ($grid[$row][$col + 1] == 'USED') {
					continue;
				}
				// if valid image, put on grid.
				if (isset($fotos[$bigindex]) && $fotos[$bigindex] instanceof Foto) {
					// if place already USED, do not put photo in.
					if ($grid[$row][$col] == 'USED') {
						continue;
					}
					$grid[$row][$col] = array(
						'index'	 => $bigindex,
						'foto'	 => $fotos[$bigindex]
					);
					// mark the three places overlapped by this image as used.
					$grid[$row + 1][$col] = $grid[$row][$col + 1] = $grid[$row + 1][$col + 1] = 'USED';
				}
			}
		}
		// put small images on grid.
		$row = $col = 0;
		foreach ($fotos as $key => $foto) {
			// do not put big pictures on grid again.
			if (in_array($key, $this->big)) {
				continue;
			}
			// find first free place.
			while ($grid[$row][$col] != null) {
				$col = $col + 1;
				// move to next row if end of row is reached.
				if ($col >= $this->per_row) {
					$row = $row + 1;
					$col = $col % $this->per_row;
					// break out of two loops if reached row limit.
					if ($row >= $this->rows) {
						break 2;
					}
				}
			}
			$grid[$row][$col] = array(
				'index'	 => $key,
				'foto'	 => $foto
			);
		}
		// check length of last row and remove it if not full and no big images overlap it.
		if (!in_array('USED', end($grid)) && count(array_filter(end($grid))) < $this->per_row) {
			unset($grid[$this->rows - 1]);
		}
		if (count(array_filter(end($grid))) == 0) {
			unset($grid[count($grid) - 1]);
		}
		return $grid;
	}

	public function getGridHtml() {
		$grid = $this->getGrid();
		$url = $this->model->getUrl();
		$delta = $this->picsize + (2 * $this->rowmargin);
		$ret = '<div class="images" style="height: ' . (count($grid) * $delta) . 'px">';
		foreach ($grid as $row => $rowcontents) {
			foreach ($rowcontents as $col => $foto) {
				if (is_array($foto)) {
					$ret .= '<a href="' . $url . '#' . direncode($foto['foto']->filename) . '"';
					$ret.=in_array($foto['index'], $this->big) ? 'class="big"' : 'class="sml"';
					$ret .= 'style=" left: ' . ($delta * $col) . 'px; top: ' . ($delta * $row) . 'px;">';
					$ret .= '<img src="' . $foto['foto']->getThumbURL() . '">';
					$ret .= '</a>' . "\n";
				}
			}
		}
		$ret .= '</div>';
		return $ret;
	}

	public function getHTML() {
		$url = $this->model->getUrl();
		if ($this->compact) {
			// compacte versie van de tag is alleen een thumbnail.
			$content = '<a href="' . $url . '"><img src="' . $this->model->getThumbURL() . '" class="compact" /></a><div class="clear"></div>';
		} else {
			$content = $this->getGridHtml();
		}
		return '<div class="ubb_block ubb_fotoalbum"><h2>' . FotoAlbumView::getBreadcrumbs($this->model, false, true) . '</a></h2>' . $content . '</div>';
	}

}
