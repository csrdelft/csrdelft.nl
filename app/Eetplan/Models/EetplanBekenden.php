<?php

namespace App\Eetplan\Models;

use App\Models\BaseModel;
use App\Models\Profiel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


/**
 * App\Models\EetplanBekenden
 *
 * @property string $uid1
 * @property string $uid2
 * @property int $id
 * @method static Builder|EetplanBekenden whereUid1($value)
 * @method static Builder|EetplanBekenden whereUid2($value)
 * @method static Builder|EetplanBekenden whereId($value)
 * @mixin \Eloquent
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Profiel $noviet1
 * @property-read Profiel $noviet2
 * @method static Builder|EetplanBekenden whereCreatedAt($value)
 * @method static Builder|EetplanBekenden whereUpdatedAt($value)
 */
class EetplanBekenden extends BaseModel
{
    protected $table = 'eetplan_bekenden';

    protected $fillable = ['id', 'uid1', 'uid2'];

    public function noviet1()
    {
        return $this->hasOne(Profiel::class, 'uid', 'uid1');
    }

    public function noviet2()
    {
        return $this->hasOne(Profiel::class, 'uid', 'uid2');
    }

    public static function getBekenden($lichting)
    {
        return static::query()
            ->where('uid1', 'like', $lichting . "%")
            ->get();
    }

    public function exists()
    {
        $results = $this->getConnection()->select(
            $this->grammar->compileExists($this), $this->getBindings(), ! $this->useWritePdo
        );

        // If the results has rows, we will get the row and see if the exists column is a
        // boolean true. If there is no results for this query we will return false as
        // there are no rows for this query at all and we can return that info here.
        if (isset($results[0])) {
            $results = (array) $results[0];

            if ((bool) $results['exists']) {
                return true;
            }
        }

        $other = new static();
        $other->uid1 = $this->uid2;
        $other->uid2 = $this->uid1;

        $results = $this->getConnection()->select(
            $this->grammar->compileExists($other), $other->getBindings(), ! $other->useWritePdo
        );

        // If the results has rows, we will get the row and see if the exists column is a
        // boolean true. If there is no results for this query we will return false as
        // there are no rows for this query at all and we can return that info here.
        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }
}
