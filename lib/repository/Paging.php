<?php

namespace CsrDelft\repository;

/**
 * Paging.interface.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Paginering van items.
 *
 */
interface Paging
{

	public function getAantalPerPagina();

	public function setAantalPerPagina($aantal);

	public function getHuidigePagina();

	public function setHuidigePagina($pagina, $voor);

	public function getAantalPaginas($voor);
}
