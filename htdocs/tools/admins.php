<?php

use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\View;

require_once 'configuratie.include.php';

/**
 * admins.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 * toont lijst van beheerders met rechten-niveau
 *
 * request url: /tools/admins/
 */
if (!LoginModel::mag(P_LEDEN_READ)) {
	echo 'Niet voldoende rechten';
	exit;
}

class AdminsView implements View {

	public function view() {
		$accounts = AccountModel::instance()->find('perm_role NOT IN (?,?,?,?)', array('R_LID', 'R_NOBODY', 'R_ETER', 'R_OUDLID'), null, 'perm_role');
		?>
		<h1>Admins</h1>
		<p>
			Op deze pagina vind je een overzicht met alle leden die meer rechten op de stek hebben dan leden. In de broncode van de stek is alles te vinden over welke rechten waar gebruikt worden. Zie hier voor <a href="https://github.com/csrdelft/csrdelft.nl">github.com/csrdelft/csrdelft.nl</a>.
		</p>
		<dl>
			<dt>R_BASF</dt>
			<dd>Mag het fotoalbum modereren, documenten modereren en de bieb modereren.</dd>
			<dt>R_FISCAAT</dt>
			<dd>Mag saldi van leden zien en producten aanmaken in het civisaldo systeem.</dd>
			<dt>R_MAALCIE</dt>
			<dd>Mag alles wat R_FISCAAT mag en maaltijden modereren.</dd>
			<dt>R_FORUM_MOD</dt>
			<dd>Mag het forum modereren.</dd>
			<dt>R_VLIEGER</dt>
			<dd>Mag alles wat R_MAALCIE mag en alles wat R_BASF mag.</dd>
			<dt>R_BESTUUR</dt>
			<dd>Mag alles wat R_MAALCIE mag, alles wat R_BASF mag en het forum modereren, de agenda modereren, de courant
				beheren, peilingen beheren en in forum belangrijk posten.
			</dd>
			<dt>R_PUBCIE</dt>
			<dd>Mag alles. Oftewel alles wat R_BESTUUR mag, forum delen maken, pagina's maken, menu beheren, eetplan beheren
				en de courant versturen.
			</dd>
		</dl>
		<table class="table">
			<thead>
			<tr>
				<th>UID</th>
				<th>Naam</th>
				<th>Rechten</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($accounts as $account) {
				echo "<tr><td>{$account->uid}</td><td>{$account->getProfiel()->getLink()}</td><td>{$account->perm_role}</td></tr>";
			}
			?>
			</tbody>
		</table>

		<?php
	}

	public function getTitel() {
		return "Admins";
	}

	public function getBreadcrumbs() {
		return '';
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		return null;
	}
}

(new \CsrDelft\view\CsrLayoutPage(new AdminsView))->view();
