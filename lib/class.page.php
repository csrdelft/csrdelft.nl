<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.page.php
# -------------------------------------------------------------------
# Page is de klasse waarbinnen een pagina in elkaar wordt gezooid
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

require_once('class.simplehtml.php');

class Page extends SimpleHTML {

	### private ###

	# Een column is een kolom met een bepaalde breedte
	var $_columns = array();

	### public ###
	function addColumn(&$column, $bTopic=false){ 
		$this->_columns[] =& $column;
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		print(<<<EOT
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN' 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html>

<head>

<title>C.S.R. Delft</title>

<meta http-equiv='content-type' content='text/xhtml; charset=UTF-8' />
<meta name="keywords" content="student studeren studie delft tu tudelft csr c.s.r. csrdelft christen christenen nederland gezelligheid gezellig" />
<meta name="description" content="C.S.R. Delft - Vereniging van christenstudenten in Delft" />
<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="/csrdelft.css" type="text/css" />
<link rel="alternate" title="C.S.R.-Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/forum/rss.php" />
<script type="text/javascript">
// Textarea's groter maken met behulp van hun id. 'rows' wordt aan het aantal regels toegevoegd.
function vergrootTextarea(id, rows) {
  var textarea = document.getElementById(id);
  //if (!textarea || (typeof(textarea.rows) == "undefined")) return;
  var currentRows=textarea.rows;
  textarea.rows = currentRows + rows;
}
</script>

</head>

<body>

<table style="margin-left: auto; margin-right: auto; width: 1000px;" border="0">

<tr>
<td style="width: 1000px;" align="center"><img src="/images/balkmoses.jpg" width="909" height="125" alt="C.S.R. Delft - Vereniging van christenstudenten" /></td>
</tr>

</table>

<table style="margin-left: auto; margin-right: auto; width: 1000px;" border="0">
<tr>
EOT
		);

		# de kolommen met inhoud neergooien
		foreach ($this->_columns as $column) $column->view();

		print(<<<EOT

</tr>
</table>



</body>
</html>
EOT
		);
	}

}

?>
