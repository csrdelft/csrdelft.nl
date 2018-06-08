<?php

namespace App\Models;

use function CsrDelft\aaidrom;
use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\leden\BestuursLedenModel;
use CsrDelft\model\groepen\leden\CommissieLedenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use function CsrDelft\square_crop;
use CsrDelft\view\bbcode\CsrBB;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Profiel
 *
 * @property string $uid
 * @property string|null $nickname
 * @property string|null $duckname
 * @property string $voornaam
 * @property string|null $tussenvoegsel
 * @property string $achternaam
 * @property string $voorletters
 * @property string|null $postfix
 * @property string $adres
 * @property string $postcode
 * @property string $woonplaats
 * @property string $land
 * @property string|null $telefoon
 * @property string $mobiel
 * @property string $geslacht
 * @property string|null $voornamen
 * @property string|null $echtgenoot
 * @property string|null $adresseringechtpaar
 * @property string|null $linkedin
 * @property string|null $website
 * @property string|null $beroep
 * @property string|null $studie
 * @property string|null $patroon
 * @property int|null $studienr
 * @property int|null $studiejaar
 * @property int $lidjaar
 * @property string|null $lidafdatum
 * @property string $gebdatum
 * @property string|null $sterfdatum
 * @property string|null $bankrekening
 * @property int|null $machtiging
 * @property string|null $moot
 * @property string|null $verticale
 * @property int|null $verticaleleider
 * @property int|null $kringcoach
 * @property string|null $o_adres
 * @property string|null $o_postcode
 * @property string|null $o_woonplaats
 * @property string|null $o_land
 * @property string|null $o_telefoon
 * @property string $email
 * @property string|null $kerk
 * @property string|null $muziek
 * @property string $status
 * @property string|null $eetwens
 * @property int|null $corvee_punten
 * @property int|null $corvee_punten_bonus
 * @property string $ontvangtcontactueel
 * @property string|null $kgb
 * @property string $changelog
 * @property string $ovkaart
 * @property string|null $zingen
 * @property string $novitiaat
 * @property int $lengte
 * @property string|null $vrienden
 * @property string $middelbareSchool
 * @property string $novietSoort
 * @property string $matrixPlek
 * @property string $startkamp
 * @property string|null $medisch
 * @property string|null $novitiaatBijz
 * @method static Builder|Profiel whereAchternaam($value)
 * @method static Builder|Profiel whereAdres($value)
 * @method static Builder|Profiel whereAdresseringechtpaar($value)
 * @method static Builder|Profiel whereBankrekening($value)
 * @method static Builder|Profiel whereBeroep($value)
 * @method static Builder|Profiel whereChangelog($value)
 * @method static Builder|Profiel whereCorveePunten($value)
 * @method static Builder|Profiel whereCorveePuntenBonus($value)
 * @method static Builder|Profiel whereDuckname($value)
 * @method static Builder|Profiel whereEchtgenoot($value)
 * @method static Builder|Profiel whereEetwens($value)
 * @method static Builder|Profiel whereEmail($value)
 * @method static Builder|Profiel whereGebdatum($value)
 * @method static Builder|Profiel whereGeslacht($value)
 * @method static Builder|Profiel whereKerk($value)
 * @method static Builder|Profiel whereKgb($value)
 * @method static Builder|Profiel whereKringcoach($value)
 * @method static Builder|Profiel whereLand($value)
 * @method static Builder|Profiel whereLengte($value)
 * @method static Builder|Profiel whereLidafdatum($value)
 * @method static Builder|Profiel whereLidjaar($value)
 * @method static Builder|Profiel whereLinkedin($value)
 * @method static Builder|Profiel whereMachtiging($value)
 * @method static Builder|Profiel whereMatrixPlek($value)
 * @method static Builder|Profiel whereMedisch($value)
 * @method static Builder|Profiel whereMiddelbareSchool($value)
 * @method static Builder|Profiel whereMobiel($value)
 * @method static Builder|Profiel whereMoot($value)
 * @method static Builder|Profiel whereMuziek($value)
 * @method static Builder|Profiel whereNickname($value)
 * @method static Builder|Profiel whereNovietSoort($value)
 * @method static Builder|Profiel whereNovitiaat($value)
 * @method static Builder|Profiel whereNovitiaatBijz($value)
 * @method static Builder|Profiel whereOAdres($value)
 * @method static Builder|Profiel whereOLand($value)
 * @method static Builder|Profiel whereOPostcode($value)
 * @method static Builder|Profiel whereOTelefoon($value)
 * @method static Builder|Profiel whereOWoonplaats($value)
 * @method static Builder|Profiel whereOntvangtcontactueel($value)
 * @method static Builder|Profiel whereOvkaart($value)
 * @method static Builder|Profiel wherePatroon($value)
 * @method static Builder|Profiel wherePostcode($value)
 * @method static Builder|Profiel wherePostfix($value)
 * @method static Builder|Profiel whereStartkamp($value)
 * @method static Builder|Profiel whereStatus($value)
 * @method static Builder|Profiel whereSterfdatum($value)
 * @method static Builder|Profiel whereStudie($value)
 * @method static Builder|Profiel whereStudiejaar($value)
 * @method static Builder|Profiel whereStudienr($value)
 * @method static Builder|Profiel whereTelefoon($value)
 * @method static Builder|Profiel whereTussenvoegsel($value)
 * @method static Builder|Profiel whereUid($value)
 * @method static Builder|Profiel whereVerticale($value)
 * @method static Builder|Profiel whereVerticaleleider($value)
 * @method static Builder|Profiel whereVoorletters($value)
 * @method static Builder|Profiel whereVoornaam($value)
 * @method static Builder|Profiel whereVoornamen($value)
 * @method static Builder|Profiel whereVrienden($value)
 * @method static Builder|Profiel whereWebsite($value)
 * @method static Builder|Profiel whereWoonplaats($value)
 * @method static Builder|Profiel whereZingen($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Account $account
 */
class Profiel extends BaseModel
{
	protected $primaryKey = 'uid';
	public $incrementing = false;
	protected $keyType = 'string';
    protected $table = 'profielen';

    public function getNaam($vorm = 'civitas') {
        if ($vorm === 'user') {
            $vorm = LidInstellingenModel::get('forum', 'naamWeergave');
        }
        switch ($vorm) {

            case 'leeg':
                $naam = '';
                break;

            case 'volledig':
                if (empty($this->voornaam)) {
                    $naam = $this->voorletters . ' ';
                } else {
                    $naam = $this->voornaam . ' ';
                }
                if (!empty($this->tussenvoegsel)) {
                    $naam .= $this->tussenvoegsel . ' ';
                }
                $naam .= $this->achternaam;
                break;

            case 'streeplijst':
                $naam = $this->achternaam . ', ';
                if (!empty($this->tussenvoegsel)) {
                    $naam .= $this->tussenvoegsel . ', ';
                }
                $naam .= $this->voornaam;
                break;

            case 'voorletters':
                $naam = $this->voorletters . ' ';
                if (!empty($this->tussenvoegsel)) {
                    $naam .= $this->tussenvoegsel . ' ';
                }
                $naam .= $this->achternaam;
                break;

            case 'bijnaam':
                if (!empty($this->nickname)) {
                    $naam = $this->nickname;
                    break;
                }
                $naam = $this->getNaam('civitas');
                break;

            case 'Duckstad':
                if (!empty($this->duckname)) {
                    $naam = $this->duckname;
                    break;
                }
                $naam = $this->getNaam('civitas');
                break;

            case 'civitas':
                // noviet
                if ($this->status === LidStatus::Noviet) {
                    $naam = 'Noviet ' . $this->voornaam;
                    if (!empty($this->postfix)) {
                        $naam .= ' ' . $this->postfix;
                    }
                } elseif ($this->isLid() OR $this->isOudlid()) {
                    // voor novieten is het Dhr./ Mevr.
                    if (Auth::user()->profiel->status === LidStatus::Noviet) {
                        $naam = ($this->geslacht === Geslacht::Vrouw) ? 'Mevr. ' : 'Dhr. ';
                    } else {
                        $naam = ($this->geslacht === Geslacht::Vrouw) ? 'Ama. ' : 'Am. ';
                    }
                    if (!empty($this->tussenvoegsel)) {
                        $naam .= ucfirst($this->tussenvoegsel) . ' ';
                    }
                    $naam .= $this->achternaam;
                    if (!empty($this->postfix)) {
                        $naam .= ' ' . $this->postfix;
                    }
                    // status char weergeven bij oudleden en ereleden
                    if ($this->isOudlid()) {
                        $naam .= ' ' . LidStatus::getChar($this->status);
                    }
                } // geen lid
                else {
                    if (Auth::user()->hasPermission('P_LEDEN_READ')) {
                        $naam = $this->voornaam . ' ';
                    } else {
                        $naam = $this->voorletters . ' ';
                    }
                    if (!empty($this->tussenvoegsel)) {
                        $naam .= $this->tussenvoegsel . ' ';
                    }
                    $naam .= $this->achternaam;
                    // status char weergeven bij kringels
                    if ($this->status === LidStatus::Kringel) {
                        $naam .= ' ' . LidStatus::getChar($this->status);
                    }
                }

                break;

            case 'aaidrom': // voor een 1 aprilgrap ooit
                $naam = aaidrom($this->voornaam, $this->tussenvoegsel, $this->achternaam);
                break;

            default:
                throw new CsrException('Onbekend naam formaat ' . htmlspecialchars($vorm));
        }
        return $naam;
    }

    public function getLink($vorm = 'civitas') {
        if (!LoginModel::mag('P_LEDEN_READ') OR in_array($this->uid, array('x999', 'x101', 'x027', 'x222', '4444'))) {
            if ($vorm === 'pasfoto' AND LoginModel::mag('P_LEDEN_READ')) {
                return $this->getPasfotoTag();
            }
            return $this->getNaam();
        }
        $naam = $this->getNaam($vorm);
        if ($vorm === 'pasfoto') {
            $naam = $this->getPasfotoTag();
        } elseif ($this->lidjaar === 2013) {
            $naam = CsrBB::parse('[neuzen]' . $naam . '[/neuzen]');
        }
        $k = '';
        if ($vorm !== 'pasfoto' AND LidInstellingenModel::get('layout', 'visitekaartjes') == 'ja') {
            $title = '';
        } else {
            $title = ' title="' . htmlspecialchars($this->getNaam('volledig')) . '"';
        }
        $l = '<a href="/profiel/' . $this->uid . '"' . $title . ' class="lidLink ' . htmlspecialchars($this->status) . '">';
        if ($vorm !== 'pasfoto' AND ($vorm === 'leeg' OR LidInstellingenModel::get('layout', 'visitekaartjes') == 'ja')) {
            $k .= '<span';
            if ($vorm !== 'leeg') {
                $k .= ' class="hoverIntent"';
            }
            $k .= '><div style="margin-top: -15px; margin-left: -15px;" class="';
            if ($vorm !== 'leeg') {
                $k .= 'hoverIntentContent ';
            }
            $k .= 'visitekaartje';
            if ($this->isJarig()) {
                $k .= ' jarig';
            }
            if ($vorm === 'leeg') {
                $k .= '" style="display: block; position: static;';
            } else {
                $k .= ' init';
            }
            $k .= '">';
            $k .= $this->getPasfotoTag(false);
            $k .= '<div class="uid uitgebreid"><a href="/gesprekken/?zoek=' . urlencode($this->getNaam('civitas')) . '" class="lichtgrijs" title="Gesprek"><span class="glyphicon glyphicon-comment" aria-hidden="true"></span></a></div>';
            if (AccountModel::existsUid($this->uid) AND LoginModel::instance()->maySuTo($this->account->toLegacy())) {
                $k .= '<div class="uid uitgebreid">';
                $k .= '<a href="/su/' . $this->uid . '" title="Su naar dit lid">' . $this->uid . '</a>';
                $k .= '</div>';
            }
            $k .= '<p class="naam">' . $l . $this->getNaam('volledig') . '&nbsp;' . LidStatus::getChar($this->status);
            $k .= '</a></p>';
            $k .= '<p>' . $this->lidjaar;
            $verticale = $this->getVerticale();
            if ($verticale) {
                $k .= ' ' . $verticale->naam;
            }
            $k .= '</p>';
            $bestuurslid = BestuursLedenModel::instance()->find('uid = ?', array($this->uid), null, null, 1)->fetch();
            if ($bestuurslid) {
                $bestuur = BesturenModel::get($bestuurslid->groep_id);
                $k .= '<p><a href="' . $bestuur->getUrl() . '">' . GroepStatus::getChar($bestuur->status) . ' ' . $bestuurslid->opmerking . '</a></p>';
            }
            foreach (CommissieLedenModel::instance()->find('uid = ?', array($this->uid), null, 'lid_sinds DESC') as $commissielid) {
                $commissie = CommissiesModel::get($commissielid->groep_id);
                if ($commissie->status === GroepStatus::HT) {
                    $k .= '<p>';
                    if (!empty($commissielid->opmerking)) {
                        $k .= $commissielid->opmerking . '<br />';
                    }
                    $k .= '<a href="' . $commissie->getUrl() . '">' . $commissie->naam . '</a></p>';
                }
            }
            $k .= '</div>';
            if ($vorm === 'leeg') {
                $naam = $k . $naam;
            } else {
                $naam = $k . $l . $naam . '</a>';
            }
            return '<div class="inline">' . $naam . '</span></div>';
        }
        return $l . $naam . '</a>';
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'uid', 'uid');
    }

    public function isLid() {
        return LidStatus::isLidLike($this->status);
    }

    public function isOudlid() {
        return LidStatus::isOudlidLike($this->status);
    }

    public function isJarig() {
        return substr($this->gebdatum, 5, 5) === date('m-d');
    }

    public function getVerticale() {
        return VerticalenModel::get($this->verticale);
    }

    public function getPasfotoTag($cssClass = 'pasfoto', $vierkant = false) {
        return '<img class="' . htmlspecialchars($cssClass) . '" src="/plaetjes/' . $this->getPasfotoPath($vierkant) . '" alt="Pasfoto van ' . $this->getNaam('volledig') . '" />';
    }

    /**
     * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
     * Geef anders een standaard-plaatje terug.
     *
     * @param boolean $square Geef een pad naar een vierkante (150x150px) versie terug. (voor google contacts sync)
     * @return string
     */
    public function getPasfotoPath($vierkant = false, $vorm = 'user') {
        $path = null;
        if (LoginModel::mag('P_OUDLEDEN_READ')) {
            // in welke (sub)map moeten we zoeken?
            if ($vierkant) {
                $folders = array('');
            } else {
                if ($vorm === 'user') {
                    $vorm = LidInstellingenModel::get('forum', 'naamWeergave');
                }
                $folders = array($vorm . '/', '');
            }
            // loop de volgende folders af op zoek naar de gevraagde pasfoto vorm
            foreach ($folders as $subfolder) {
                foreach (array('png', 'jpeg', 'jpg', 'gif') as $validExtension) {
                    if (file_exists(PHOTOS_PATH . 'pasfoto/' . $subfolder . $this->uid . '.' . $validExtension)) {
                        $path = 'pasfoto/' . $subfolder . $this->uid . '.' . $validExtension;
                        break;
                    }
                }
                if ($path) {
                    break;
                } elseif ($vorm === 'Duckstad') {
                    $path = 'pasfoto/' . $vorm . '/eend.jpg';
                    break;
                }
            }
        }
        if (!$path) {
            $path = 'pasfoto/geen-foto.jpg';
        }
        // als het vierkant moet, kijken of de vierkante bestaat, en anders maken
        if ($vierkant) {
            $crop = 'pasfoto/' . $this->uid . '.vierkant.png';
            if (!file_exists(PHOTOS_PATH . $crop)) {
                square_crop(PHOTOS_PATH . $path, PHOTOS_PATH . $crop, 150);
            }
            return $crop;
        }
        return $path;
    }

    public static function getNovieten($lichting = null)
    {
        $query = static::query()
            ->where('status', 'S_NOVIET');

        if ($lichting != null) {
            $query = $query->where('uid', 'like', $lichting . '%');
        }

        return $query->get();
    }
}
