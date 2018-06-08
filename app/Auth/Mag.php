<?php

namespace App\Auth;

use CsrDelft\model\security\LoginModel;

/**
 * Check of een gebruiker mag wat hij wil doen. Is een trait zodat op een gegeven moment hier de logica geswapt kan
 * worden voor die van Laravel.
 *
 * Gebruik de @see \App\Http\Middleware\CheckPermission middleware als dat mogelijk is.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 03/04/2018
 */
trait Mag
{
    protected function mag($permissie) {
        if (LoginModel::mag($permissie)) {
            return;
        }

        abort(401, __('auth.permission_denied'));
    }

    protected function magOrRedirect($permissie, $url = '/') {
        if (LoginModel::mag($permissie)) {
            return;
        }

        redirect($url);
    }

    protected function magOrError($permissie, $status = 401) {
        if (LoginModel::mag($permissie)) {
            return;
        }

        abort($status, __('auth.permission_denied'));
    }
}