<?php

namespace App\Policies;

use App\Models\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    public function before(Account $user, $ability)
    {
        if ($user->isPubCie()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the account.
     *
     * @param  \App\Models\Account $user
     * @param  \App\Models\Account $account
     * @return mixed
     */
    public function view(Account $user, Account $account)
    {
        return $user->uid == $account->uid;
    }

    /**
     * Determine whether the user can create accounts.
     *
     * @param  \App\Models\Account $user
     * @return mixed
     */
    public function create(Account $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the account.
     *
     * @param  \App\Models\Account $user
     * @param  \App\Models\Account $account
     * @return mixed
     */
    public function update(Account $user, Account $account)
    {
        return $user->uid == $account->uid;
    }

    /**
     * Determine whether the user can delete the account.
     *
     * @param  \App\Models\Account $user
     * @param  \App\Models\Account $account
     * @return mixed
     */
    public function delete(Account $user, Account $account)
    {
        return false;
    }
}
