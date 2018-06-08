<?php

namespace App\Console\Commands;

use CsrDelft\model\entity\Mail;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\pin\PinTransactieDownloader;
use CsrDelft\model\fiscaat\pin\PinTransactieMatcherFactory;
use CsrDelft\model\fiscaat\pin\PinTransactieModel;
use Exception;
use Illuminate\Console\Command;

/**
 * pin_transactie_download.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */
class PinTransactieDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pin_transactie:run {datum : Datum van de borrel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download en match pintransacties.';

    /** @var string Username setting. */
    private $username;
    /** @var string Password setting. */
    private $password;
    /** @var string Store setting. */
    private $store;
    /** @var string Url setting. */
    private $url;
    /** @var string Monitoring email setting. */
    private $monitoringEmail;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $settings = parse_ini_file(base_path('etc/pin_transactie_download.ini'));

        $this->username = $settings[self::SETTINGS_USERNAME];
        $this->password = $settings[self::SETTINGS_PASSWORD];
        $this->store = $settings[self::SETTINGS_STORE];
        $this->url = $settings[self::SETTINGS_URL];
        $this->monitoringEmail = $settings[self::SETTINGS_MONITORING_EMAIL];
    }

    /**
     * Date constants.
     */
    const DATE_FORMAT = 'Y-m-d';
    const DURATION_DAY_IN_SECONDS = 86400;

    /**
     * Settings constants.
     */
    const SETTINGS_USERNAME = 'username';
    const SETTINGS_PASSWORD = 'password';
    const SETTINGS_STORE = 'store';
    const SETTINGS_URL = 'url';
    const SETTINGS_MONITORING_EMAIL = 'monitoring_email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->hasArgument('datum')) {
            $moment = strtotime($this->argument('datum'));
            $interactive = true;
        } else {
            $moment = time() - self::DURATION_DAY_IN_SECONDS;
            $interactive = false;
        }

        $from = date(self::DATE_FORMAT . ' 12:00:00', $moment - self::DURATION_DAY_IN_SECONDS);
        $to = date(self::DATE_FORMAT . ' 12:00:00', $moment);

        // Verwijder eerdere download.
        $vorigePinTransacties = PinTransactieModel::instance()->getPinTransactieInMoment($from, $to);

        foreach ($vorigePinTransacties as $pinTransactie) {
            PinTransactieModel::instance()->delete($pinTransactie);
        }


        // Download pintransacties en sla op in DB.
        $pintransacties = PinTransactieDownloader::download($this->url, $this->store, $this->username, $this->password, $moment);

        // Haal pinbestellingen op.
        $pinbestellingen = CiviBestellingModel::instance()->getPinBestellingInMoment($from, $to);

        try {
            $matcher = new PinTransactieMatcherFactory($pintransacties, $pinbestellingen);

            $matcher->clean();
            $matcher->match();
            $matcher->save();


            if ($matcher->bevatFouten()) {
                $report = $matcher->genereerReport();

                $body = <<<MAIL
Beste am. Fiscus,

Zojuist zijn de pin transacties en bestellingen tussen {$from} en {$to} geanalyseerd.

De volgende fouten zijn gevonden.

{$report}

Met vriendelijke groet,

namense de PubCie,
Feut
MAIL;
                if ($interactive) {
                    echo $body;
                    echo "\n\nDe email is niet verzonden, want de sessie is in interactieve modus.\n";
                    echo sprintf("Er zijn %d pin transacties gedownload.\n", count($pintransacties));

                } else {
                    $mail = new Mail([$this->monitoringEmail => 'Pin Transactie Monitoring'], '[CiviSaldo] Pin transactie fouten gevonden.', $body);
                    $mail->send();
                }
            }

        } catch (Exception $e) {
            if ($interactive) {
                echo $e->getMessage() . "\n";
                echo $e->getTraceAsString();
            } else {
                // Throw naar shutdownhandler.
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $e;
            }
        }
    }
}
