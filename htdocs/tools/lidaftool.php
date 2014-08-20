<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN,groep:soccie,groep:maalcie')) {
	redirect(CSR_ROOT);
}

echo '<h1>Overzicht saldi</h1><form action="/tools/lidaftool.php" method="post">';

if (isset($_POST['namenRaw'])) {
	$aUids = namen2uid($_POST['namenRaw']);
	if (is_array($aUids) AND count($aUids) != 0) {
		echo '<table border="0">';
		echo '<tr><th style="width: 300px;">Naam</hd>';
		echo '<th style="width: 100px;">SocCie</th><th style="width: 110px;">MaalCie</th><th style="width: 200px;">Abo\'s</th>';
		echo '<th>Abo\'s weg</th><th>status naar</th>';
		echo '</tr>';

		foreach ($aUids as $aLid) {
			if (isset($aLid['uid'])) {
				$lid = LidCache::getLid($aLid['uid']);
				//naam is gevonden en uniek, dus direct goed.
				$saldi = $lid->getSaldi();
				echo '<tr>';
				echo '<td ><input type="hidden" name="naam[]" value="' . $aLid['uid'] . '" />' . $aLid['naam'] . '</td>';
				foreach ($lid->getSaldi() as $saldo) {
					echo '<td style="text-align: right;';
					if ($saldo['saldo'] < 0) {
						echo ' color: red;';
					}
					echo '">' . sprintf('&euro; %01.2f', $saldo['saldo']) . '</td>';
				}

				require_once 'maalcie/model/MaaltijdAbonnementenModel.class.php';
				$abos = MaaltijdAbonnementenModel::getAbonnementenVoorLid($aLid['uid']);
				echo '<td>' . print_r($abos) . '</td>';
				echo '<td><input type="checkbox" name="delabos[]" /></td>';
				echo '<td><select name="status[]"><option value="S_NOBODY">Lid af</option><option value="S_OUDLID">Oudlid</option></select></td>';
				echo '</tr>';
			} else {
				//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
				if (count($aLid['naamOpties']) > 0) {
					echo '<tr><td colspan="3"><select name="naam[]" class="tekst">';
					foreach ($aLid['naamOpties'] as $aNaamOptie) {
						echo '<option value="' . $aNaamOptie['uid'] . '">' . $aNaamOptie['naam'] . '</option>';
					}
					echo '</select></td>';
				}//dingen die niets opleveren wordt niets voor weergegeven.
			}
		}
		echo '</table>';
	}
} else {
	echo '<textarea name="namenRaw" rows="10" cols="40"></textarea>';
}

echo '<input type="submit" name="submit" value="Opslaan" /></form>';


