/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import Bloodhound from 'corejs-typeahead';
import Dropzone from 'dropzone/dist/dropzone-amd-module';
import $ from 'jquery';

window.$ = window.jQuery = $;

/**
 * jQuery extensies registreren zichzelf aan bovenstaande jQuery.
 */
require('bootstrap');
require('./ajax-csrf');
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
import {fnUpdateDataTable} from './datatable-api';
import {forumBewerken, saveConceptForumBericht} from './forum';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './maalcie';
import {ketzerAjax} from './ajax';
import {importAgenda} from './courant';

/**
 * Globale objecten gebruikt in PHP code.
 */
$.extend(window, {
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
        // See blade_templates/forum/partial/post_form.blade.php
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
        // See blade_templates/forum/partial/post_lijst.blade.php
        forumBewerken,
        // See blade_templates/forum/partial/post_forum.blade.php
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
    courant: {
        // See templates/courant/courantbeheer.tpl
        importAgenda,
    }
});

Dropzone.autoDiscover = false;

$.widget.bridge('uitooltip', $.ui.tooltip);
