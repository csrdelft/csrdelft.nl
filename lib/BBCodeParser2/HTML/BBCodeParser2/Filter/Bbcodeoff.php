<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * @package  HTML_BBCodeParser2
 * @author   Gerrit Uitslag <klapinklapin@gmail.com>
 */


/**
 * Filter for tag to switch off bbcode
 */
class HTML_BBCodeParser2_Filter_Bbcodeoff extends HTML_BBCodeParser2_Filter {

	/**
	 * An array of tags parsed by the engine
	 *
	 * @var      array
	 */
	protected $_definedTags = array(
		'ubboff' => array( 	'htmlopen'  => '',
							'htmlclose' => '',
							'allowed'   => 'none',
							'attributes'=> array('')),
		'tekst' => array( 	'htmlopen'  => '',
							'htmlclose' => '',
							'allowed'   => 'none',
							'attributes'=> array(''))
	);


}
