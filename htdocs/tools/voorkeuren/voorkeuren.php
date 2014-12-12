<?php

require_once 'configuratie.include.php';

redirect(CSR_ROOT . '/profiel/' . LoginModel::getUid() . '/voorkeuren');
