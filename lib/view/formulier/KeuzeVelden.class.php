<?php

/**
 * KeuzeVelden.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Bevat o.a. de <SELECT> uitbreidingen van InputField:
 *
 *    - SelectField
 *        * GeslachtField                m/v
 *        * JaNeeField                ja/nee
 *        * VerticaleField            Verticalen
 *        * KerkField                    Denominaties
 *        * RadioField                Keuzerondje
 *
 *    - CheckboxField                    Keuzevakje
 *    - DateField                        Datum
 *    - TimeField                        Tijsstip
 *  - ColorField                    Kleurkiezer
 *  - SterrenField                        Sterren
 *
 * Pas op, de volgorde van requires is belangrijk
 */

require_once 'view/formulier/keuzevelden/SelectField.class.php';
require_once 'view/formulier/keuzevelden/RadioField.class.php';
require_once 'view/formulier/keuzevelden/CheckboxField.class.php';
require_once 'view/formulier/keuzevelden/ColorField.class.php';
require_once 'view/formulier/keuzevelden/DateField.class.php';
require_once 'view/formulier/keuzevelden/DateTimeField.class.php';
require_once 'view/formulier/keuzevelden/EntityDropDown.class.php';
require_once 'view/formulier/keuzevelden/GeslachtField.class.php';
require_once 'view/formulier/keuzevelden/JaNeeField.class.php';
require_once 'view/formulier/keuzevelden/KerkField.class.php';
require_once 'view/formulier/keuzevelden/MultiSelectField.class.php';
require_once 'view/formulier/keuzevelden/SterrenField.class.php';
require_once 'view/formulier/keuzevelden/TimeField.class.php';
require_once 'view/formulier/keuzevelden/VerticaleField.class.php';
require_once 'view/formulier/keuzevelden/WeekdagField.class.php';
require_once 'view/formulier/keuzevelden/RequiredCheckboxField.class.php';
require_once 'view/formulier/keuzevelden/RequiredColorField.class.php';
require_once 'view/formulier/keuzevelden/RequiredDateField.class.php';
require_once 'view/formulier/keuzevelden/RequiredDateTimeField.class.php';
require_once 'view/formulier/keuzevelden/RequiredEntityDropDown.class.php';
require_once 'view/formulier/keuzevelden/RequiredGeslachtField.class.php';
require_once 'view/formulier/keuzevelden/RequiredJaNeeField.class.php';
require_once 'view/formulier/keuzevelden/RequiredKerkField.class.php';
require_once 'view/formulier/keuzevelden/RequiredRadioField.class.php';
require_once 'view/formulier/keuzevelden/RequiredSelectField.class.php';
require_once 'view/formulier/keuzevelden/RequiredSterrenField.class.php';
require_once 'view/formulier/keuzevelden/RequiredTimeField.class.php';
require_once 'view/formulier/keuzevelden/RequiredVerticaleField.class.php';
require_once 'view/formulier/keuzevelden/RequiredWeekdagField.class.php';