<?php

require_once 'configuratie.include.php';

invokeRefresh(CSR_ROOT . '/communicatie/profiel/' . LoginModel::getUid() . '/voorkeuren');
