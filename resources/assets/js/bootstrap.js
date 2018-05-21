/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import _ from 'lodash';

import Bloodhound from 'typeahead.js';
import Dropzone from 'dropzone/dist/dropzone-amd-module';
import $ from 'jquery';

window.$ = window.jQuery = $;

/**
 * jQuery extensies registreren zichzelf aan bovenstaande jQuery.
 */
require('bootstrap');
require('jgallery/dist/js/jgallery'); // jGallery moet na de bootstrap geladen worden! Ondersteund geen CommonJS.
require('jquery-hoverintent');
require('jquery.scrollto');
require('jquery-ui');
require('jquery-ui/ui/effect');
require('jquery-ui/ui/effects/effect-highlight');
require('jquery-ui/ui/effects/effect-fade');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/slider');
require('jquery-ui/ui/widgets/tooltip');
require('jquery-ui/ui/widgets/tabs');
require('./lib/jquery.markitup');
require('./lib/jquery.contextMenu');
require('timeago');
require('raty-js');
require('autosize/build/jquery.autosize');
require('./lib/jquery.formSteps');
require('./lib/jquery-ui-sliderAccess');
require('jquery-ui-timepicker-addon');
require('./lib/jquery-ui-timepicker-nl');
require('jquery.maskedinput');

import {basename, dirname, randomIntFromInterval, redirect, reload, selectText} from './util';
import {bbvideoDisplay, CsrBBPreview} from './bbcode';
import {formCancel, formInlineToggle, formSubmit} from './formulier';
import initContext, {domUpdate} from './context';
import {fnUpdateDataTable} from './datatable';
import {forumBewerken, saveConceptForumBericht} from './forum';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './maalcie';
import {ketzerAjax} from './ajax';
import {peilingBevestigStem} from './peiling';
import {importAgenda} from './courant';

/**
 * Globale objecten gebruikt in PHP code.
 */
_.assign(window, {
    _,
    Bloodhound,
    Dropzone,
    util: {
        // See templates/fotoalbum/album.tpl
        basename,
        // See templates/fotoalbum/album.tpl
        dirname,
        // See templates/fotoalbum/slider.tpl
        randomIntFromInterval,
        // See templates/fotoalbum/album.tpl
        redirect,
        // See templates/fotoalbum/album.tpl
        reload,
        // See templates/fotoalbum/album.tpl
        selectText,
    },
    bbcode: {
        // See view/formulier/invoervelden/BBCodeField.class.php
        // See templates/roodschopper/roodschopper.tpl
        // See templates/mededelingen/mededeling.tpl
        // See templates/courant/courantbeheer.tpl
        // See template/forum/post_form.tpl
        CsrBBPreview,
        // See view/bbcode/CsrBB.class.php
        bbvideoDisplay,
    },
    formulier: {
        // See templates/instellingen/beheer/instelling_row.tpl
        formInlineToggle,
        // See view/formulier/invoervelden/InputField.abstract.php
        // See view/formulier/invoervelden/ZoekField.class.php
        formSubmit,
        // See view/formulier/invoervelden/InputField.abstract.php
        formCancel,
    },
    context: {
        // See view/formulier/invoervelden/LidField.class.php
        initContext,
        // See view/groepen/leden/GroepTabView.class.php
        domUpdate,
    },
    // See view/formulier/datatable/DataTable.php
    fnUpdateDataTable,
    // See templates/maalcie/maaltijd/maaltijd_ketzer.tpl
    ketzerAjax,
    forum: {
        // See templates/forum/post_lijst.tpl
        forumBewerken,
        // See templates/forum/post_forum.tpl
        saveConceptForumBericht,
    },
    maalcie: {
        // See templates/maalcie/corveetaak/beheer_taak_datum.tpl
        // See templates/maalcie/corveetaak/beheer_taak_head.tpl
        takenToggleDatum,
        // See templates/maalcie/corveetaak/beheer_taken.tpl
        takenShowOld,
        // See templates/maalcie/corveetaak/suggesties_lijst.tpl
        // See view/maalcie/forms/SuggestieLijst.php
        takenToggleSuggestie,
        // See view/maalcie/forms/SuggestieLijst.php
        takenColorSuggesties,
    },
    peiling: {
        // See templates/peiling/peiling.bb.tpl
        peilingBevestigStem,
    },
    courant: {
        // See templates/courant/courantbeheer.tpl
        importAgenda,
    }
});

Dropzone.autoDiscover = false;

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
    numbers: [],
};

$.widget.bridge('uitooltip', $.ui.tooltip);