<?php
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;

require_once 'configuratie.include.php';

/**
 * admins.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * toont lijst van beheerders met rechten-niveau
 * 
 * request url: /tools/admins/
 */
if (!LoginModel::mag('P_LEDEN_READ')) {
	echo 'Niet voldoende rechten';
	exit;
}

$accounts = AccountModel::instance()->find('perm_role IN (?,?,?,?)', array('R_BASF', 'R_MAALCIE', 'R_BESTUUR', 'R_PUBCIE'), null, 'perm_role');
?>
<html>
	<body>
		<table>
			<thead>
				<tr><th>UID</th><th>Naam</th><th>Rechten</th></tr>
			</thead>
			<tbody>
				<?php
				foreach ($accounts as $account) {
					echo "<tr><td>{$account->uid}</td><td>{$account->getProfiel()->getTitel()}</td><td>{$account->perm_role}</td></tr>";
				}
				?>
			</tbody>
		</table>
	</body>
</html>