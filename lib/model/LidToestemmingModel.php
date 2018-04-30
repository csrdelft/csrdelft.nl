<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\LidInstelling;
use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;


/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de toestemming bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidToestemmingModel extends InstellingenModel {

    const ORM = LidToestemming::class;

    /**
     * 'module' => array( 'key' => array('beschrijving', 'type', type-opties, 'default value', technical-values) )
     *
     * type-opties:
     * enum: array
     * int: array( min, max )
     * string: array( min-lenght, max-lenght )
     *
     * technical-values:
     * array(type-optie-1 => technical-value-1, ...)
     *
     * @var array
     */
    protected static $defaults = [
        'intern' => [
            // MaalCie
//             'abo' => ['Abonnement', T::Enumeration, ['', 'ja', 'nee'], ''],  // Alleen maalcie
//            'voorkeuren' => ['Corveevoorkeuren', T::Enumeration, ['', 'ja', 'nee'], ''],  // Alleen maalcie
//            'kwalificaties' => ['Kwalificaties', T::Enumeration, ['', 'ja', 'nee'], ''],  // Alleen maalcie
            'eetwens' => ['Allergie/dieet', T::Enumeration, ['', 'ja', 'nee'], ''],
//            'corvee' => ['Corveepunten en -taken', T::Enumeration, ['', 'ja', 'nee'], ''], // Alleen maalcie

            // C.S.R. Groepen
            'commissies' => ['Commissies', T::Enumeration, ['', 'ja', 'nee'], ''],
            'werkgroepen' => ['Werkgroepen', T::Enumeration, ['', 'ja', 'nee'], ''],
            'ondervereniging' => ['Ondervereniging', T::Enumeration, ['', 'ja', 'nee'], ''],
            'groepen' => ['Groepen', T::Enumeration, ['', 'ja', 'nee'], ''],
            'activiteiten' => ['Recent bezochte activiteiten', T::Enumeration, ['', 'ja', 'nee'], ''],
            'kring' => ['Kring', T::Enumeration, ['', 'ja', 'nee'], ''],
            'verticale' => ['Verticale', T::Enumeration, ['', 'ja', 'nee'], ''],
            'ketzers' => ['Aanschafketzers', T::Enumeration, ['', 'ja', 'nee'], ''], // Niet in bestuursdocument

            // C.S.R. overig
            'forum_posts' => ['Forumbijdragen', T::Enumeration, ['', 'ja', 'nee'], ''],
            'status' => ['Lidstatus', T::Enumeration, ['', 'ja', 'nee'], ''],
            'patroon' => ['Patroon/matroon', T::Enumeration, ['', 'ja', 'nee'], ''],
            'kinderen' => ['Verenigingskinderen', T::Enumeration, ['', 'ja', 'nee'], ''],

            // Gegevens
            'naam' => ['Naam', T::Enumeration, ['', 'ja', 'nee'], ''],
            'bijnaam' => ['Bijnaam', T::Enumeration, ['', 'ja', 'nee'], ''],
            'voorletters' => ['Voorletters', T::Enumeration, ['', 'ja', 'nee'], ''],
            'gebdatum' => ['Geboortedatum', T::Enumeration, ['', 'ja', 'nee'], ''],
            'adres' => ['Woonadres', T::Enumeration, ['', 'ja', 'nee'], ''],
            'telefoon' => ['Telefoonnummer', T::Enumeration, ['', 'ja', 'nee'], ''],
            'mobiel' => ['Mobiele nummer', T::Enumeration, ['', 'ja', 'nee'], ''],
            'o_adres' => ['Woonadres ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
            'o_telefoon' => ['Telefoonnummer ouders', T::Enumeration, ['', 'ja', 'nee'], ''],
            'email' => ['E-mailadres', T::Enumeration, ['', 'ja', 'nee'], ''],
            'studie' => ['Studie', T::Enumeration, ['', 'ja', 'nee'], ''],
            'studiejaar' => ['Studielichting', T::Enumeration, ['', 'ja', 'nee'], ''],
            'profielfoto' => ['Profielfoto', T::Enumeration, ['', 'ja', 'nee'], ''],
            'bankrekening' => ['Bankrekeningnummer', T::Enumeration, ['', 'ja', 'nee'], ''],
            'fotos' => ['Getagde foto\'s', T::Enumeration, ['', 'ja', 'nee'], ''],
            'beroep' => ['Beroep', T::Enumeration, ['', 'ja', 'nee'], ''], // Niet in bestuursdocument
        ],
        'extern' => [
            'foto' => ['Foto op externe stek', T::Enumeration, ['', 'ja', 'nee'], ''],
        ]
    ];

    /**
     * Functie getInstelling aanvullen met uid.
     *
     * @param array $primary_key_values
     * @return LidInstelling|false
     */
    protected function retrieveByPrimaryKey(array $primary_key_values) {
        $primary_key_values[] = LoginModel::getUid();
        return parent::retrieveByPrimaryKey($primary_key_values);
    }

    protected function newInstelling($module, $id) {
        $instelling = new LidToestemming();
        $instelling->module = $module;
        $instelling->instelling_id = $id;
        $instelling->waarde = $this->getDefault($module, $id);
        $instelling->uid = LoginModel::getUid();
        $this->create($instelling);
        return $instelling;
    }

    public static function toestemmingGegeven() {
        if ($_SERVER['REQUEST_URI'] == '/privacy') // Doe niet naggen op de privacy info pagina.
            return true;

        $uid = LoginModel::getUid();

        return static::instance()->count('uid = ? AND waarde <> \'\'', [$uid]) > 0;
    }

    public function toestemming($profiel, $id, $except = 'P_LEDEN_MOD') {
        if ($profiel->uid == LoginModel::getUid())
            return true;

        if (LoginModel::mag($except))
            return true;

        /** @var LidToestemming $toestemming */
        $toestemming = parent::retrieveByPrimaryKey(['toestemming', $id, $profiel->uid]);

        if (!$toestemming)
            return false;

        return $toestemming->waarde == "ja";
    }

    public function getDescription($module, $id) {
        return static::$defaults[$module][$id][0];
    }

    public function getType($module, $id) {
        if (static::has($module, $id)) {
            return static::$defaults[$module][$id][1];
        } else {
            return null;
        }
    }

    public function getTypeOptions($module, $id) {
        return static::$defaults[$module][$id][2];
    }

    public function getDefault($module, $id) {
        return static::$defaults[$module][$id][3];
    }

    public function isValidValue($module, $id, $waarde) {
        $options = $this->getTypeOptions($module, $id);
        switch ($this->getType($module, $id)) {
            case T::Enumeration:
                if (in_array($waarde, $options)) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function save() {
        // create matrix for sqlInsertMultiple
        $properties[] = $this->getAttributes();
        foreach (static::$defaults as $module => $instellingen) {
            foreach ($instellingen as $id => $waarde) {
                if ($this->getType($module, $id) === T::Integer) {
                    $filter = FILTER_SANITIZE_NUMBER_INT;
                } else {
                    $filter = FILTER_SANITIZE_STRING;
                }
                $waarde = filter_input(INPUT_POST, $module . '_' . $id, $filter);
                if (!$this->isValidValue($module, $id, $waarde)) {
                    continue;
                }
                $properties[] = array($module, $id, $waarde, LoginModel::getUid());
            }
        }
        Database::instance()->sqlInsertMultiple($this->getTableName(), $properties, true);
        $this->flushCache(true);
    }
}
