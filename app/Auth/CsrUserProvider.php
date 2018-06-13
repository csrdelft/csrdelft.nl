<?php

namespace App\Auth;

use App\Models\RememberToken;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CsrUserProvider extends EloquentUserProvider
{
	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		return $this->hasher->check($credentials['password'], $user->getAuthPassword());
	}

    /**
     * Haal gebruiker op met een token.
     *
     * @param mixed $identifier
     * @param string $token
     * @return Authenticatable|null
     */
	public function retrieveByToken($identifier, $token)
    {
        $rememberToken = (new RememberToken)->where('token', $token)->first();

        if ($rememberToken && $rememberToken->uid == (int) $identifier) {
            return $rememberToken->account;
        }

        return null;
    }

    /**
     * @param Authenticatable $user
     * @param string $token
     * @throws \Exception
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        list($device, $token) = explode('|', $token);

        $oldRememberToken = (new RememberToken)
            ->where('device_name', $device)
            ->where($user->getAuthIdentifierName(), $user->getAuthIdentifier())
            ->first();

        if ($oldRememberToken && $token == '') {
            $oldRememberToken->delete();

            return;
        }

        if ($oldRememberToken) {
            $oldRememberToken->token = $token;
            $oldRememberToken->save();
        } else {
            $rememberToken = new RememberToken;
            $rememberToken->device_name = $device;
            $rememberToken->token = $token;
            $rememberToken->uid = $user->getAuthIdentifier();
            $rememberToken->save();
        }
    }
}
