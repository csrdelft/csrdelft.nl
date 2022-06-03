<?php

namespace CsrDelft\common;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170824
 */
class CsrGebruikerException extends CsrException
{
    public function __construct($message = "")
    {
        parent::__construct($message, 400);
    }
}
