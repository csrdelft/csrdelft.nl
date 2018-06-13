<?php

namespace App\Eetplan\Models;

use App\Models\BaseModel;
use App\Models\Profiel;
use Carbon\Carbon;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * App\Models\Eetplan
 *
 * @property string $uid
 * @property int $woonoord_id
 * @property string|null $avond
 * @property int $id
 * @method static Builder|Eetplan whereAvond($value)
 * @method static Builder|Eetplan whereId($value)
 * @method static Builder|Eetplan whereUid($value)
 * @method static Builder|Eetplan whereWoonoordId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Profiel $noviet
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @method static Builder|Eetplan whereCreatedAt($value)
 * @method static Builder|Eetplan whereUpdatedAt($value)
 */
class Eetplan extends BaseModel
{
    protected $table = 'eetplan';
    protected $fillable = ['uid', 'avond', 'woonoord_id'];

    /**************
     * Relationships
     */

    /**
     * @return HasOne
     */
    public function noviet()
    {
        return $this->hasOne(Profiel::class, 'uid', 'uid');
    }

    /**
     * @return PersistentEntity|false
     */
    public function woonoord()
    {
        return WoonoordenModel::get($this->woonoord_id);
    }

    /*************************
     * Class Methods
     */

    /**
     * @param string $lichting
     * @return string[]
     */
    public static function getAvonden(string $lichting)
    {
        return static::query()
            ->where('uid', 'like', $lichting . '%')
            ->whereNotNull('avond')
            ->orderBy('avond')
            ->get()
            ->map(function (Eetplan $eetplan) {
                return [$eetplan->avond, $eetplan->avond];
            })
            ->unique()
            ->toAssoc()
            ->all();
    }

    /**
     * @param string $lichting
     * @return array
     */
    public static function getEetplan(string $lichting)
    {
        $eetplan = static::query()
            ->where('uid', 'like', $lichting . '%')
            ->whereNotNull('avond')
            ->orderBy('avond')
            ->get();

        $eetplanFeut = [];
        $avonden = [];
        foreach ($eetplan as $sessie) {
            if (!isset($eetplanFeut[$sessie->uid])) {
                $eetplanFeut[$sessie->uid] = (object)[
                    'avonden' => [],
                    'noviet' => $sessie->noviet,
                ];
            }

            if (!isset($avonden[$sessie->avond])) {
                $avonden[$sessie->avond] = $sessie->avond;
            }

            $eetplanFeut[$sessie->uid]->avonden[] = $sessie->woonoord();
        }

        return array_values($eetplanFeut);
    }

    /**
     * @param Profiel $profiel
     * @return Collection|static[]
     */
    public static function getEetplanVoorNoviet(Profiel $profiel)
    {
        return static::query()
            ->where('uid', $profiel->uid)
            ->get();
    }

    /**
     * @param string $lichting
     * @return Collection|static[]
     */
    public static function getBekendeHuizen(string $lichting)
    {
        return static::query()
            ->where('uid', 'like', $lichting . '%')
            ->whereNull('avond')
            ->get();
    }

    /**
     * @param int $woonoord
     * @param string $lichting
     * @return Collection|static[]
     */
    public static function getEetplanVoorHuis(int $woonoord, string $lichting)
    {
        return static::query()
            ->where('uid', 'like', $lichting . '%')
            ->where('woonoord_id', $woonoord)
            ->whereNotNull('avond')
            ->orderBy('avond')
            ->get();
    }

    /**
     * @param string $lichting
     * @return Collection|static[]
     */
    public static function getBezocht(string $lichting)
    {
        return static::query()
            ->where('uid', 'like', $lichting . '%')
            ->get();
    }

    /**
     * @param string $avond
     * @return Collection|static[]
     */
    public static function getEetplanVoorAvond(string $avond)
    {
        return static::query()
            ->where('avond', $avond)
            ->get();
    }
}
