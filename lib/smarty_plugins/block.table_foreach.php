<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {table_foreach} block function plugin
 *
 * Type:     block function<br>
 * Name:     table_foreach<br>
 * Date:     Jan 5, 2005<br>
 * Purpose:  make an html table with designable cells from an associative array.
 *           'key' and 'item' available as in foreach.
 *           Elements of the array can be scalars, arrays, or objects, and can be accessed in the cells.
 *           Supports nested calls<br>
 * Input:<br>
 *         - from = array to loop through
 *         - item = The name of the variable that is the current element
 *         - key  = The name of the variable that is the current key
 *
 *         - cols = number of columns
 *         - rows = number of rows
 *         - table_attr = table attributes
 *         - tr_attr = table row attributes (arrays are cycled)
 *         - td_attr = table cell attributes (arrays are cycled)
 *         - trailpad = value to pad trailing cells with
 *         - vdir = vertical direction (default: "down", means top-to-bottom)
 *         - hdir = horizontal direction (default: "right", means left-to-right)
 *         - inner = inner loop (default "cols": print $loop line by line,
 *                   $loop will be printed column by column otherwise)
 *
 * Examples:
 * $bookList is an array of objects.
 *
 * <pre>
 *{table_foreach from=$bookList item=book key=id cols=4 td_attr='bgcolor="#FFEEDD"'}
 *   The book with id {$id} is entitled "{$book->bookname}".
 *{/table_foreach}
 * </pre>
 * @author   Benjamin Layet <benjamin@moonfactory.co.jp>
 * @version  1.0
 * @link http://smarty.php.net/manual/en/language.function.html.table.php {html_table}
 *       http://smarty.php.net/manual/en/language.function.foreach.php    {foreach}
 *          (Smarty online manual)
 * @return string
 */

function smarty_block_table_foreach($params, $content, &$smarty, &$repeat) {
	static $depth = 0;          //depth in a nested situation :
	// 1 in general, 2 in the first nested call, 3 in the second etc...

	static $from_array = array(); //keep track of the from param, and the current value. Index is $depth.
	static $loop_array = array(); //feed the html_table function. Index is $depth.


	///////////////////////////////////////////////////////
	//check params
	///////////////////////////////////////////////////////
	if (!isset($params['from'])) {
		$smarty->trigger_error("table_foreach: missing 'from' parameter");
		return;
	}
	if (!isset($params['item'])) {
		$smarty->trigger_error("table_foreach: missing 'item' parameter");
		return;
	}

	if (0 == count($params['from'])) {
		return;
	}

	///////////////////////////////////////////////////////
	//first call (for each {table_foreach } in the tpl)
	//increment $depth
	///////////////////////////////////////////////////////
	if (is_null($content)) {
		$depth++;
		$from_array[$depth] = (array)$params['from'];
	}
	///////////////////////////////////////////////////////
	//assignments of $content for next call
	///////////////////////////////////////////////////////

	//get the from param corresponding to the current depth
	$from = &$from_array[$depth];

	//get the current key and item
	//$from being static the array descriptor is kept
	//between two calls
	if (list($key, $item) = each($from)) {
		$repeat = true;
		//smarty assignments : item
		$item_varname = (string)$params['item'];
		$smarty->assign($item_varname, $item);

		//key
		if (isset($params['key'])) {
			$key_varname = (string)$params['key'];
			$smarty->assign($key_varname, $key);
		}
	} else {
		//no values left
		$repeat = false;
	}

	///////////////////////////////////////////////////////
	//add content to loop[]
	///////////////////////////////////////////////////////
	if (!is_null($content)) {
		$loop = &$loop_array[$depth];
		$loop[] = $content;
	}

	///////////////////////////////////////////////////////
	//last call : call html_table
	//decrement $depth
	///////////////////////////////////////////////////////
	if (!$repeat) {

		//call html_table
		require_once(dirname(__FILE__) . "/function.html_table.php");
		$params = array_merge($params, array("loop" => $loop));

		//reset the static vars
		$loop = array();
		$from = array();
		//decrement $depth
		$depth--;

		return smarty_function_html_table($params, $smarty);

	}
}
/* vim: set expandtab: */
