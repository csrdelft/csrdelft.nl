<?php

namespace App\Auth;

use App\Models\Account;
use CsrDelft\model\entity\security\Account as LegacyAccount;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use Illuminate\Auth\SessionGuard as BaseSessionGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Str;
use Illuminate\Auth\Events;

/**
 * Custom SessionGuard, kan meerdere RememberTokens tegelijk hebben.
 *
 * Slaat een device id op naast de sessie, op basis hiervan kan de gebruiker meerdere sessies hebben en kan de
 * sessie die bij de device hoort volledig verwijderd worden. (ook van afstand)
 *
 * Werkt samen met @see \App\Auth\CsrUserProvider . Let op met de waarde van de tokens. Deze kan van de vorm
 * <device>|<token> zijn, op deze manier blijven de interfaces kloppen.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 17/03/2018
 */
class CsrSessionGuard extends BaseSessionGuard
{
    /**
     * @param Account|LegacyAccount $account
     * @return bool
     */
    public function maySuTo($account)
    {
        if ($account instanceof Account) {
            $oldAccount = new LegacyAccount;
            $oldAccount->uid = $account->uid;
            $oldAccount->perm_role = $account->perm_role;
        } else {
            $oldAccount = $account;
        }

        return LoginModel::mag('P_ADMIN') AND !$this->isSued() AND $account->uid !== $this->user()->uid AND AccessModel::mag($oldAccount, 'P_LOGGED_IN');
    }

    public function suedFrom()
    {
        return session('suedFrom');
    }

    public function isSued()
    {
        return session('suedFrom') != null;
    }

    public function mag(string $permissie, array $allowedAuthenticationMethods = null)
    {
        /** @var Account $account */
        $account = $this->user();

        return AccessModel::mag($account->toLegacy(), $permissie, $allowedAuthenticationMethods);
    }

    /**
     * Log a user into the application.
     *
     * @see \Illuminate\Auth\SessionGuard::login
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(AuthenticatableContract $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            $this->saveDeviceId();
            $this->ensureRememberTokenIsSet($user);
            $this->queueRecallerCookie($user);
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * Log the user out of the application.
     *
     * @see \Illuminate\Auth\SessionGuard::logout
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        if (! is_null($user)) {
            $this->removeRememberToken($user);
        }

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        if (isset($this->events)) {
            $this->events->dispatch(new Events\Logout($user));
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Save a unique identifier for this device.
     */
    protected function saveDeviceId()
    {
        $this->session->put('device', Str::random(10));
    }

    /**
     * @param AuthenticatableContract $user
     */
    public function removeRememberToken(AuthenticatableContract $user) {
        $device = $this->session->get('device');

        $this->provider->updateRememberToken($user, $device . '|');
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @param AuthenticatableContract $user
     */
    public function cycleRememberToken(AuthenticatableContract $user)
    {
        $user->setRememberToken($token = Str::random(60));

        $deviceId = $this->session->get('device');

        $this->provider->updateRememberToken($user, $deviceId . '|' . $token);
    }
}