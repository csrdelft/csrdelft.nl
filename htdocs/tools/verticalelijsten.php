<?php

# Verticalenlijsten maken

use CsrDelft\common\MijnSqli;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

if (!LoginModel::mag(P_ADMIN)) {
	redirect(CSR_ROOT);
}
$db = MijnSqli::instance();

echo '<table cellpadding="15"><tr valign="top">';
$verticalen = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');
foreach ($verticalen as $letter) {
	$result = $db->select("SELECT uid FROM profielen WHERE verticale=" . $letter . " AND (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')");
	if ($result !== false and $db->numRows($result) > 0) {
		echo '<td><h3>Verticale ' . VerticalenModel::get($letter)->naam . '</h3><pre>';

		while ($row = $db->next($result)) {
			echo $row['uid'] . "@csrdelft.nl\n";
		}

		echo '</pre></td>';
	}
}
echo '</tr></table>';
