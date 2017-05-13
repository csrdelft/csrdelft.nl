<?php
use CsrDelft\Orm\Persistence\Database;

require_once 'configuratie.include.php';
require_once 'model/fiscaat/CiviSaldoModel.class.php';
require_once 'model/fiscaat/CiviBestellingModel.class.php';

if (!LoginModel::mag('P_ADMIN')) die('Geen toegang');

Database::transaction(function () {
    // Iedereen van lichting 2007 en eerder
    $oud = CiviSaldoModel::instance()->find('id < 266');

    foreach ($oud as $lid) {
        /** @var CiviSaldo $lid */
        if ($lid->saldo === 0) {
            // Al goed
            echo sprintf("Lid %s op nul<br>", $lid->uid);
        } elseif ($lid->saldo > 0) {
            echo sprintf("Lid %s positief<br>", $lid->uid);
            $bestelling = new CiviBestelling();
            $bestelling->cie = 'anders';
            $bestelling->uid = $lid->uid;
            $bestelling->deleted = false;
            $bestelling->moment = getDateTime();

            $inhoud = new CiviBestellingInhoud();
            $inhoud->aantal = $lid->saldo;
            $inhoud->product_id = 6; // TODO dynamic, is cent

            $bestelling->inhoud[] = $inhoud;
            $bestelling->totaal = $lid->saldo;

            CiviBestellingModel::instance()->create($bestelling);

            CiviSaldoModel::instance()->verlagen($lid->uid, $lid->saldo);
            $lid->saldo = 0;
            $lid->laatst_veranderd = getDateTime();
        } elseif ($lid->saldo < 0) {
            echo sprintf("Lid %s negatief<br>", $lid->uid);
            $inleg = $lid->saldo * -1;
            $bestelling_model = CiviBestellingModel::instance();

            $bestelling = $bestelling_model->vanInleg($inleg, $lid->uid);
            $bestelling_model->create($bestelling);

            CiviSaldoModel::instance()->ophogen($lid->uid, $inleg);
            $lid->saldo += $inleg;
            $lid->laatst_veranderd = getDateTime();
        }

        $lid->deleted = true;

        CiviSaldoModel::instance()->update($lid);
    }
});
