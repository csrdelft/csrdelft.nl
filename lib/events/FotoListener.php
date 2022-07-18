<?php

namespace CsrDelft\events;

use CsrDelft\entity\fotoalbum\Foto;
use Doctrine\ORM\Mapping\PostLoad;

/**
 * Verantwoordelijk voor laden van directory in foto bij ophalen uit database
 */
class FotoListener
{
	/** @PostLoad */
	public function postLoadHandler(Foto $foto)
	{
		$foto->directory = join_paths(PHOTOALBUM_PATH, $foto->subdir);
	}
}
