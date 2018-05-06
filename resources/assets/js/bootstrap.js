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

_.assign(window, {
    ...utils,
    ...bbcode,
    _,
    Bloodhound,
    Dropzone,
});

/**
 * Laad jQuery in globale scope.
 */
window.$ = window.jQuery = require('jquery');

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
require('jquery-ui/ui/widgets/tooltip');
require('jquery-ui/ui/widgets/tabs');
require('./lib/jquery.markitup');
require('./lib/jquery.contextMenu');
require('timeago');

