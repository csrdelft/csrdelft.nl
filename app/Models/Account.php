<?php

namespace App\Models;

use App\Auth\CsrUserProvider;
use Carbon\Carbon;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Account
 *
 * @property string $uid
 * @property string $username
 * @property string $email
 * @property string $pass_hash
 * @property string $pass_since
 * @property string|null $last_login_success
 * @property string|null $last_login_attempt
 * @property int $failed_login_attempts
 * @property string|null $blocked_reason
 * @property string $perm_role
 * @property string|null $private_token
 * @property string|null $private_token_since
 * @property string|null $remember_token
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read Profiel $profiel
 * @method static Builder|Account whereBlockedReason($value)
 * @method static Builder|Account whereEmail($value)
 * @method static Builder|Account whereFailedLoginAttempts($value)
 * @method static Builder|Account whereLastLoginAttempt($value)
 * @method static Builder|Account whereLastLoginSuccess($value)
 * @method static Builder|Account wherePassHash($value)
 * @method static Builder|Account wherePassSince($value)
 * @method static Builder|Account wherePermRole($value)
 * @method static Builder|Account wherePrivateToken($value)
 * @method static Builder|Account wherePrivateTokenSince($value)
 * @method static Builder|Account whereRememberToken($value)
 * @method static Builder|Account whereUid($value)
 * @method static Builder|Account whereUpdatedAt($value)
 * @method static Builder|Account whereUsername($value)
 * @mixin \Eloquent
 */
class Account extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Notifiable, Authenticatable, Authorizable, CanResetPassword;

	public $timestamps = false;

    public function getAuthPassword() {
			return $this->pass_hash;
    }

	public function getAuthIdentifierName()
	{
		return 'uid';
	}

	public function getAuthIdentifier()
	{
		return $this->uid;
	}

    protected $table = 'accounts';
	protected $primaryKey = 'uid';
    public $incrementing = false;
    protected $keyType = 'string';

    public function isPubCie()
    {
        return $this->perm_role == 'R_PUBCIE';
    }

    /**
     * Wordt afgehandeld door @see CsrUserProvider
     *
     * @param string $token
     */
    public function setRememberToken($token)
    {
    }

    /**
     * Zoek de remember token op voor dit account gebasseerd op uid en device.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        $rememberToken = (new RememberToken)
            ->where($this->getAuthIdentifierName(), $this->getAuthIdentifier())
            ->where('device_name', session()->get('device'))
            ->first();

        if ($rememberToken) {
            return $rememberToken->token;
        }

        return null;
    }

    public function profiel()
    {
        return $this->hasOne(Profiel::class, 'uid', 'uid');
    }

    public static function existsByUid($uid)
    {
        return static::query()
            ->where('uid', $uid)
            ->exists();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function toLegacy() {
        $legacyAccount = new \CsrDelft\model\entity\security\Account();
        $legacyAccount->uid = $this->uid ?? 'x999';
        $legacyAccount->perm_role = $this->perm_role ?? 'P_PUBLIC';

        return $legacyAccount;
    }

    public function hasPermission($permissie) {
        return AccessModel::mag($this->toLegacy(), $permissie);
    }
}
