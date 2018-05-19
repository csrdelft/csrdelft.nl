/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import _ from 'lodash';

/**
 * Globale objecten gebruikt in PHP code.
 */
import Bloodhound from 'typeahead.js';
import Dropzone from 'dropzone/dist/dropzone-amd-module';
import * as bbcode from './bbcode';
import * as utils from './util';
import $ from 'jquery';

import {formInlineToggle, formSubmit, formCancel} from './formulier';

import initContext, {domUpdate} from './context';

_.assign(window, {
    ...utils,
    ...bbcode,
    _,
    $,
    jQuery: $,
    Bloodhound,
    Dropzone,
    // See resources/templates/instellingen/beheer/instelling_row.tpl
    formInlineToggle,
    // See view/formulier/invoervelden/InputField.abstract.php
    // See view/formulier/invoervelden/ZoekField.class.php
    formSubmit,
    // See view/formulier/invoervelden/InputField.abstract.php
    formCancel,
    // See view/formulier/invoervelden/LidField.class.php
    initContext,
    domUpdate
});

Dropzone.autoDiscover = false;

/**
 * jQuery extensies registreren zichzelf aan bovenstaande jQuery.
 */
import 'bootstrap';
import 'jgallery/dist/js/jgallery'; // jGallery moet na de bootstrap geladen worden! Ondersteund geen CommonJS.
import 'jquery-hoverintent';
import 'jquery.scrollto';
import 'jquery-ui';
import 'jquery-ui/ui/effect';
import 'jquery-ui/ui/effects/effect-highlight';
import 'jquery-ui/ui/effects/effect-fade';
import 'jquery-ui/ui/widgets/tooltip';
import 'jquery-ui/ui/widgets/tabs';
import './lib/jquery.markitup';
import './lib/jquery.contextMenu';
import 'timeago';

$.timeago.settings.strings = {
    prefiprefixAgo: '',
    prefixFromNow: 'sinds',
    suffixAgo: 'geleden',
    suffixFromNow: '',
    seconds: 'nog geen minuut',
    minute: '1 minuut',
    minutes: '%d minuten',
    hour: '1 uur',
    hours: '%d uur',
    day: '1 dag',
    days: '%d dagen',
    month: '1 maand',
    months: '%d maanden',
    year: '1 jaar',
    years: '%d jaar',
    wordSeparator: ' ',
    numbers: []
};