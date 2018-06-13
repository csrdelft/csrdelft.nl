<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;


/**
 * App\Models\RememberToken
 *
 * @property int $id
 * @property string $token
 * @property string $uid
 * @property string $remember_since
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @property string $device_name
 * @property string $ip
 * @property int|null $lock_ip
 * @property-read \App\Models\Account $account
 * @method static Builder|RememberToken whereCreatedAt($value)
 * @method static Builder|RememberToken whereDeviceName($value)
 * @method static Builder|RememberToken whereId($value)
 * @method static Builder|RememberToken whereIp($value)
 * @method static Builder|RememberToken whereLockIp($value)
 * @method static Builder|RememberToken whereRememberSince($value)
 * @method static Builder|RememberToken whereToken($value)
 * @method static Builder|RememberToken whereUid($value)
 * @method static Builder|RememberToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RememberToken extends BaseModel
{
    protected $table = 'login_remember';
    public $timestamps = false;

    public function account()
    {
        return $this->hasOne(Account::class, 'uid', 'uid');
    }
}
