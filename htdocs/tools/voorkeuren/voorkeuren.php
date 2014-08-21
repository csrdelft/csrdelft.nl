<?php

require_once 'configuratie.include.php';

redirect(CSR_ROOT . '/communicatie/profiel/' . LoginModel::getUid() . '/voorkeuren');
