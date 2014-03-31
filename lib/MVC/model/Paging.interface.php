<?php

/**
 * Paging.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Paginering van items.
 * 
 */
interface Paging {

	public function getHuidigePagina();

	public function setHuidigePagina($int);

	public function getAantalPerPagina();

	public function getAantalPaginas($voor);
}
