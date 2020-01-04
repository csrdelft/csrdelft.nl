<?php


namespace CsrDelft\model\entity;


use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class ForumPlaatje extends PersistentEntity
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $access_key;

	public $datum_toegevoegd;

	public $maker;

	public $source_url;

	public function getAfbeelding($resize = false) {
		return new Afbeelding($this->getPath($resize));
	}

	public function exists() {
		return $this->getAfbeelding()->exists();
	}

	public function getUrl($resized = false) {
		return "/forum/plaatjes/bekijken/$this->access_key".($resized ? "/resized" : "");
	}

	public function getPath($resize = false) {
		return PLAATJES_PATH . ($resize ? "resized/" : "") . strval($this->id);
	}

	public function hasResized() {
		$path = $this->getPath(true);
		return file_exists($path) && is_file($path);
	}

	public function createResized() {
		// Resize the smallest side of the image to at most 1024px
		$command = IMAGEMAGICK . ' ' . escapeshellarg($this->getPath(false)) . ' -resize "750x>" -format jpg -quality 85 -interlace Line  -auto-orient ' . escapeshellarg($this->getPath(true));
		shell_exec($command);
		if ($this->hasResized()) {
			chmod($this->getPath(true), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getPath(true));
		}
	}

	/**
	 * @var string
	 */
	protected static $table_name = 'forumplaatjes';

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'access_key' => [T::StringKey, false],
		'maker' => [T::UID, true],
		'datum_toegevoegd' => [T::DateTime, false],
		'source_url' => [T::Text, true],
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}
