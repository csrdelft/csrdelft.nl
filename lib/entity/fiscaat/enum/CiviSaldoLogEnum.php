<?php

namespace CsrDelft\entity\fiscaat\enum;

use CsrDelft\common\Enum;

/**
 * CiviSaldoLogEnum.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
class CiviSaldoLogEnum extends Enum
{

    /** Maken van een bestelling */
    const INSERT_BESTELLING = 'insert';
    /** Verwijderen van een bestelling */
    const REMOVE_BESTELLING = 'remove';

    /** Aanmaken van een Saldo */
    const CREATE_SALDO = 'create';
    /** Veranderen van een saldo */
    const UPDATE_SALDO = 'update';
    /** Verwijderen van een saldo */
    const DELETE_SALDO = 'delete';
}
