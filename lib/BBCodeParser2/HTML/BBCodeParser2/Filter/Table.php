<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author
 */



/**
 * Filter for basic formatting
 */
class HTML_BBCodeParser2_Filter_Table extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'table' => array(
			'htmlopen'  		=> 'table class="bb-table" style="',
			'htmlopen_postfix' 	=> '"',
			'htmlclose' 		=> 'table',
			'allowed'   		=> 'all',
            'child'     		=> 'none^tr',
			'attributes'		=> array(
				'border' 			=> ' border: %1$s',     //note: atm only the first style is seen by browser
				'color' 			=> ' color: %1$s',
				'background-color' 	=> ' background-color: %1$s',
				'border-collapse' 	=> ' border-collapse: %1$s')),
		'tr' => array(
			'htmlopen'  => 'tr',
			'htmlclose' => 'tr',
			'allowed'   => 'all',
			'parent'    => 'none^table',
			'child'     => 'none^th,td',
			'attributes'=> array()),
		'th' => array(
			'htmlopen'  => 'th',
			'htmlclose' => 'th',
			'allowed'   => 'none^b,s,i,u,tekst,ubboff,1337,neuzen,sup,sub,email,url,lid,h,quote,color,size,font',
			'parent'    => 'none^tr',
			'attributes'=> array(
				'w' => ' style="width: %1$dpx"'
			)),
		'td' => array(
			'htmlopen'  => 'td',
			'htmlclose' => 'td',
			'allowed'   => 'none^b,s,i,u,tekst,ubboff,1337,neuzen,sup,sub,email,url,lid,h,quote,color,size,font',
			'parent'    => 'none^tr',
			'attributes'=> array(
				'w' => ' style="width: %1$dpx"'
			))

	);

}
