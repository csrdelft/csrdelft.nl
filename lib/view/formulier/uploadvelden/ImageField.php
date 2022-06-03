<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\Map;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class ImageField extends FileField {

	protected $vierkant;
	protected $minWidth;
	protected $minHeight;
	protected $maxWidth;
	protected $maxHeight;
	private $filterMime;

	public function __construct($name, $description, Afbeelding $behouden = null, Map $dir = null, array $filterMime = null, $vierkant = false, $minWidth = null, $minHeight = null, $maxWidth = 10000, $maxHeight = 10000) {
		$this->filterMime = $filterMime === null ? Afbeelding::$mimeTypes : array_intersect(Afbeelding::$mimeTypes, $filterMime);
		parent::__construct($name, $description, $behouden, $dir, $this->filterMime);
		$this->vierkant = $vierkant;
		$this->minWidth = $minWidth;
		$this->minHeight = $minHeight;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getModel() instanceof Afbeelding AND in_array($this->getModel()->mimetype, $this->filterMime)) {
			$width = $this->getModel()->width;
			$height = $this->getModel()->height;
			$resize = false;
			if ($this->vierkant AND $width !== $height) {
				$resize = 'Afbeelding is niet vierkant.';
			} else {
				if ($this->maxWidth !== null AND $width > $this->maxWidth) {
					$resize = 'Afbeelding is te breed. Maximaal ' . $this->maxWidth . ' pixels.';
					$smallerW = floor((float)$this->maxWidth * 100 / (float)$width);
				} elseif ($this->minWidth !== null AND $width < $this->minWidth) {
					$resize = 'Afbeelding is niet breed genoeg. Minimaal ' . $this->minWidth . ' pixels.';
					$biggerW = ceil((float)$this->minWidth * 100 / (float)$width);
				}
				if ($this->maxHeight !== null AND $height > $this->maxHeight) {
					$resize = 'Afbeelding is te hoog. Maximaal ' . $this->maxHeight . ' pixels.';
					$smallerH = floor((float)$this->maxHeight * 100 / (float)$height);
				} elseif ($this->minHeight !== null AND $height < $this->minHeight) {
					$resize = 'Afbeelding is niet hoog genoeg. Minimaal ' . $this->minHeight . ' pixels.';
					$biggerH = ceil((float)$this->minHeight * 100 / (float)$height);
				}
			}
			if ($resize) {
				if ($this->vierkant) {
					$percent = 'vierkant';
				} elseif (isset($biggerW, $smallerH) OR isset($biggerH, $smallerW)) {
					$this->getUploader()->error = 'Geen resize verhouding';
					return false;
				} elseif (isset($smallerW, $smallerH)) {
					$percent = min(array($smallerW, $smallerH));
				} elseif (isset($biggerW, $biggerH)) {
					$percent = max(array($biggerW, $biggerH));
				} elseif (isset($smallerW)) {
					$percent = $smallerW;
				} elseif (isset($biggerW)) {
					$percent = $biggerW;
				} elseif (isset($smallerH)) {
					$percent = $smallerH;
				} elseif (isset($biggerH)) {
					$percent = $biggerH;
				} else {
					$percent = 100;
				}
				$directory = $this->getModel()->directory;
				$filename = $this->getModel()->filename;
				$resized = $directory . $percent . $filename;
				if ($this->vierkant) {
					$command = $_ENV['IMAGEMAGICK'] . ' ' . escapeshellarg($directory . $filename) . ' -thumbnail 150x150^ -gravity center -extent 150x150 -format jpg -quality 80 ' . escapeshellarg($resized);
				} else {
					$command = $_ENV['IMAGEMAGICK'] . ' ' . escapeshellarg($directory . $filename) . ' -resize ' . $percent . '% -format jpg -quality 85 ' . escapeshellarg($resized);
				}
				if (defined('RESIZE_OUTPUT')) {
					debugprint($command);
				}
				$output = shell_exec($command);
				if (defined('RESIZE_OUTPUT')) {
					debugprint($output);
				}
				if (false === @chmod($resized, 0644)) {
					$this->getUploader()->error = $resize;
				} else {
					$this->getModel()->filename = $percent . $filename;
					if (false === unlink($directory . $filename)) {
						$this->getUploader()->error = 'Origineel verwijderen na resizen mislukt!';
					}
				}
			}
		} else {
			if ($this->required) {
				$this->getUploader()->error = 'Afbeelding is verplicht';
			}
		}
		return $this->getUploader()->error === '';
	}

}
