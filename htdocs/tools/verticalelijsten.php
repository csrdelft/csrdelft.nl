<?php

# Mootlijsten maken

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	invokeRefresh(CSR_ROOT);
}

echo <<<EOD
<table cellpadding="15">
	<tr valign="top">
EOD;

for ($i = 1; $i <= 8; $i++) {
	$result = $db->select("SELECT uid FROM `lid` WHERE verticale=" . $i . " AND (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')");
	if ($result !== false and $db->numRows($result) > 0) {
		echo '<td><h2>Verticale ' . Verticale::getNaamById($i) . '</h2><pre>';

		while ($row = $db->next($result)) {
			echo $row['uid'] . "@csrdelft.nl\n";
		}

		echo '</pre></td>';
	}
}

echo <<<EOD
	</tr>
</table>
EOD;
