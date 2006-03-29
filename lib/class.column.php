<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.column.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

require_once('class.simplehtml.php');

define('COLUMN_MENU', 175);
define('COLUMN_MIDDEN', 564);
define('COLUMN_RECHTS', 170);
define('COLUMN_MIDDENRECHTS', 766);

class Column extends SimpleHTML {

	### private ###

	var $_width = 0;

	# Een object is een van SimpleHTML afgeleid object waarin een
	# stuk pagina zit, wat we er met view() uit kunnen krijgen.
	var $_objects = array();

	### public ###

	function Column($width) {
		if (!is_int($width)) die ("Column::Column() : Need integer width!");
		$this->_width = $width;
	}

	function addObject(&$object) { $this->_objects[] =& $object; }

	# De kolom wordt afgebeeld met de juiste breedte en marge aan
	# de linkerkant. In de kolom worden de objecten onder elkaar
	# er in gegooid.
	function view() {

?>
<td style="width: 24px;"><img src="/images/pixel.gif" width="24" alt=" " /></td>
<td style="width: <?php echo $this->_width; ?>px;" class="tekst" valign="top">
<?php
//echo '<pre>'.print_r(debug_backtrace(), true).'</pre>';
		foreach ($this->_objects as $object) {
			$object->view();
?><br />
<?php
		}

?>
</td>
<?php

	}
}

?>
