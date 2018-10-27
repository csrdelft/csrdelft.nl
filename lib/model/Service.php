<?php
/**
 * Created by PhpStorm.
 * User: gerbe
 * Date: 25/09/2018
 * Time: 09:40
 */

namespace CsrDelft\model;


interface Service {
	function find($zoekFilter, $zoekVelden, $order, $limit, $start);
}
