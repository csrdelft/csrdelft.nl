<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     reldate<br>
 * Date:     May 25, 2008
 * Purpose:  Maak een relative datum van een datum.
 * Input:    $datetime > datum van invoer
 * Example:  {2008-05-25 21:55:00|reldate}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_reldate($datetime){
	$nu=time();
	$moment=strtotime($datetime);
	$verschil=$nu-$moment;
	if($verschil<=60){
		$return='<em>'.$verschil.' ';
		if($verschil==1) {$return.='seconde';}else{$return.='seconden';}
		$return.='</em> geleden';
	}elseif($verschil<=60*60){
		$return='<em>'.floor($verschil/60);
		if(floor($verschil/60)==1){	$return.=' minuut'; }else{$return.=' minuten'; }
		$return.='</em> geleden';
	}elseif($verschil<=(60*60*4)){
		$return='<em>'.floor($verschil/(60*60)).' uur</em> geleden';
	}elseif(date('Y-m-d')==date('Y-m-d', $moment)){
		$return='vandaag om '.date("G:i", $moment);
	}elseif(date('Y-m-d', $moment)==date('Y-m-d', strtotime('1 day ago'))){
		$return='gisteren om '.date("G:i", $moment);
	}else{
		$return='op '. date("G:i j-n-Y", $moment);
	}
	return $return;
}