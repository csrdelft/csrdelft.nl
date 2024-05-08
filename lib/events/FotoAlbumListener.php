<?php

namespace CsrDelft\events;

use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use Doctrine\ORM\Mapping\PostLoad;

/**
 * Verantwoordelijk voor laden van path in fotoalbum bij ophalen uit database
 */
class FotoAlbumListener
{
	#[PostLoad]
	public function postLoadHandler(FotoAlbum $album)
	{
		$album->path = PathUtil::realpathunix(
			PathUtil::join_paths(PHOTOALBUM_PATH, $album->subdir)
		);
	}
}
