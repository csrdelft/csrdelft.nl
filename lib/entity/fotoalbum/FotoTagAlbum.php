<?php

namespace CsrDelft\entity\fotoalbum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\repository\ProfielRepository;

/**
 * FotoTagAlbum.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FotoTagAlbum extends FotoAlbum
{


	/**
	 * @return int
	 *
	 * @psalm-return int<1, max>
	 */
	public function modified()
	{
		return time();
	}

	public function getParentName()
	{
		return null;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return '/fotoalbum/' . $this->uid;
	}

	/**
	 * @return true
	 */
	public function exists()
	{
		return true;
	}

	/**
	 * @return false
	 */
	public function isEmpty()
	{
		return false;
	}

	/**
	 * @return true
	 */
	public function hasFotos($incompleet = false)
	{
		return true;
	}

	/**
	 * @param false $incompleet
	 * @return Foto[]
	 */
	public function getFotos($incompleet = false)
	{
		if (!isset($this->fotos)) {
			// find tagged fotos
			$container = ContainerFacade::getContainer();
			$fotoTagsRepository = $container->get(FotoTagsRepository::class);
			$fotoRepository = $container->get(FotoRepository::class);
			foreach ($fotoTagsRepository->findBy(['keyword' => $this->uid]) as $tag) {
				$foto = $fotoRepository->retrieveByUUID($tag->refuuid);
				if ($foto) {
					$this->fotos[] = $foto;
				}
			}
		}
		return $this->fotos;
	}
}
