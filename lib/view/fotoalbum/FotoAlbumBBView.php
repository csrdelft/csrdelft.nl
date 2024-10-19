<?php
/**
 * FotoAlbumBBView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */

namespace CsrDelft\view\fotoalbum;

use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\View;

class FotoAlbumBBView implements View
{
	use ToHtmlResponse;

	private $compact = false; //compact or expanded tag.
	private $rows = 2; //number of rows
	private $per_row = 7; //images per row
	private $big = []; //array with index of the ones to enlarge
	private $picsize = 75; //size of an image
	private $rowmargin = 0.5; //margin between the images
	protected $model;

	public function __construct(FotoAlbum $fotoalbum)
	{
		// als het album alleen subalbums bevat kies een willkeurige daarvan om fotos van te tonen
		if (count($fotoalbum->getFotos()) === 0) {
			$subalbums = $fotoalbum->getSubAlbums();
			$count = count($subalbums);
			if ($count > 0) {
				$idx = random_int(0, $count - 1);
				$fotoalbum = $subalbums[$idx];
			}
		}

		$this->model = $fotoalbum;
	}

	public function __toString(): string
	{
		$html = '';
		if (count($this->model->getFotos()) < 1) {
			$html .=
				'<div class="bb-block">Fotoalbum bevat geen foto\'s: /' .
				$this->model->dirname .
				'</div>';
			return $html;
		}
		$html .= $this->getHtml();
		return $html;
	}

	public function makeCompact()
	{
		$this->compact = true;
	}

	public function setRows($rows)
	{
		$this->rows = $rows;
	}

	public function setPerRow($per_row)
	{
		$this->per_row = $per_row;
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
	public function setBig($index)
	{
		if (in_array($index, ['a', 'b', 'c'])) {
			switch ($index) {
				case 'a':
					$this->big = [0, 9, 18, 28, 37, 46];
					break;
				case 'b':
					$this->big = [0, 4, 15, 19, 28, 32, 43, 47];
					break;
				case 'c':
					$this->big = [0, 16, 4, 28, 44, 32];
					break;
			}
			return;
		}
		if (count(explode(',', $index)) > 1) {
			//explode on ',' and convert tot int.
			$this->big = array_map('intval', explode(',', $index));
		} else {
			$this->big = [(int) $index];
		}
	}

	/**
	 * Build a grid with Foto-objects.
	 *
	 * The index is saved together with the object for correct reference
	 * in case the image is moved one left or one up in the grid at borders.
	 */
	private function getGrid()
	{
		$fotos = $this->model->getFotos();
		$grid = array_fill(0, $this->rows, array_fill(0, $this->per_row, null));
		// put big images on grid.
		if (count($this->big) > 0 && $this->rows > 1) {
			foreach ($this->big as $bigindex) {
				$row = floor($bigindex / $this->per_row);
				$col = $bigindex % $this->per_row;
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
					$grid[$row][$col] = [
						'index' => $bigindex,
						'foto' => $fotos[$bigindex],
					];
					// mark the three places overlapped by this image as used.
					$grid[$row + 1][$col] = $grid[$row][$col + 1] = $grid[$row + 1][
						$col + 1
					] = 'USED';
				}
			}
		} else {
			shuffle($fotos);
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
			$grid[$row][$col] = [
				'index' => $key,
				'foto' => $foto,
			];
		}
		// check length of last row and remove it if not full and no big images overlap it.
		if (
			!in_array('USED', end($grid)) &&
			count(array_filter(end($grid))) < $this->per_row
		) {
			unset($grid[$this->rows - 1]);
		}
		if (count(array_filter(end($grid))) == 0) {
			unset($grid[count($grid) - 1]);
		}
		return $grid;
	}

	public function getGridHtml()
	{
		$grid = $this->getGrid();
		$url = $this->model->getUrl();
		$delta = $this->picsize + 2 * $this->rowmargin;
		$ret =
			'<div class="images" style="height: ' . count($grid) * $delta . 'px">';
		foreach ($grid as $row => $rowcontents) {
			foreach ($rowcontents as $col => $foto) {
				if (is_array($foto)) {
					$ret .=
						'<a href="' . $url . '#' . $foto['foto']->getResizedUrl() . '"';
					$ret .= in_array($foto['index'], $this->big)
						? 'class="big"'
						: 'class="sml"';
					$ret .=
						'style=" left: ' .
						$delta * $col .
						'px; top: ' .
						$delta * $row .
						'px;">';
					$ret .= '<img src="' . $foto['foto']->getThumbUrl() . '">';
					$ret .= '</a>' . "\n";
				}
			}
		}
		$ret .= '</div>';
		return $ret;
	}

	public function getHtml()
	{
		if ($this->compact) {
			// compacte versie van de tag is alleen een thumbnail.
			$content =
				'<a href="' .
				$this->model->getUrl() .
				'"><img src="' .
				$this->model->getCoverUrl() .
				'" class="compact" /></a><div class="clear"></div>';
		} else {
			$content = $this->getGridHtml();
		}
		return '<div class="bb-block bb-fotoalbum"><ol class="breadcrumb">' .
			FotoAlbumBreadcrumbs::getBreadcrumbs($this->model, false, true) .
			'</ol>' .
			$content .
			'</div>';
	}

	public function getTitel()
	{
		// Niet boeiend
	}

	public function getBreadcrumbs()
	{
		// Niet boeiend
	}

	public function getModel()
	{
		return $this->model;
	}
}
