<?php

namespace CsrDelft\model\entity\documenten;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class Document.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class Document extends PersistentEntity {
	public $id;
	public $naam;
	public $categorie_id;
	public $filename;
	public $filesize;
	public $mimetype;
	public $toegevoegd;
	public $eigenaar;
	public $leesrechten;

	protected static $peristent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'naam' => [T::String],
		'categorie_id' => [T::Integer],
		'filename' => [T::String],
		'filesize' => [T::Integer],
		'mimetype' => [T::String],
		'toegevoegd' => [T::DateTime],
		'eigenaar' => [T::UID],
		'leesrechten' => [T::String],
	];
	protected static $table_name = 'Document';
	protected static $primary_key = ['id'];
}
