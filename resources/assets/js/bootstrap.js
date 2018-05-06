/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
window._ = require('lodash');

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

/**
 * Globale objecten gebruikt in PHP code.
 */
window.Bloodhound = require('typeahead.js');
window.Dropzone = require('dropzone/dist/dropzone-amd-module');

require('./util');