let mix = require('laravel-mix');
let HardSourceWebpackPlugin = require('hard-source-webpack-plugin');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('htdocs/dist'); // Verwijder als Laravel draait.
mix.setResourceRoot('/dist/');

// Zolang travis nog moeilijk doet met imagemin
mix.options({
    imgLoaderOptions: {
        enabled: false
    }
});

mix.webpackConfig({
    plugins: [
        new HardSourceWebpackPlugin() // Maak build 20x sneller
    ],
    resolve: {
        extensions: ['.ts']
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                loader: 'ts-loader'
            }
        ]
    }
});


mix.js('resources/assets/js/app.js', 'js')
    .extract([
        'jquery',
        'jquery-ui',
        'jquery-ui/ui/effect',
        'jquery-ui/ui/effects/effect-highlight',
        'jquery-ui/ui/effects/effect-fade',
        'jquery-ui/ui/widgets/tooltip',
        'jquery-ui/ui/widgets/tabs',
        'jquery-ui/ui/widgets/slider',
        // 'jgallery/dist/js/jgallery', Moet gebootstrapped worden
        'corejs-typeahead',
        'dropzone/dist/dropzone-amd-module',
        'bootstrap',
        'timeago',
        'axios',
        // 'flot', Moet gebootstrapped worden
        'three',
        'datatables.net',
        'datatables.net-autofill',
        'datatables.net-buttons',
        'datatables.net-buttons/js/buttons.colVis',
        'datatables.net-buttons/js/buttons.html5',
        'datatables.net-buttons/js/buttons.flash',
        'datatables.net-buttons/js/buttons.print',
        'datatables.net-colreorder',
        'datatables.net-fixedcolumns',
        'datatables.net-fixedheader',
        'datatables.net-keytable',
        'datatables.net-responsive',
        'datatables.net-scroller',
        'datatables.net-select',
        'pako',
        'jszip',
        'parallax-js',
    ], 'js/vendor.js');

mix.js('resources/assets/js/extern.js', 'js')
    .extract([
        'jquery',
        'jquery-ui/ui/widgets/tooltip',
        'jquery-ui/ui/widgets/datepicker',
        'jquery-ui-timepicker-addon',
        'jquery-hoverintent',
        'timeago',
        'lightbox2',
    ], 'js/extern-vendor.js');

mix.js('resources/assets/js/bb-slideshow.js', 'js')
    .js('resources/assets/js/ledenmemory.js', 'js')
    .sass('resources/assets/sass/bredeletters.scss', 'css')
    .sass('resources/assets/sass/extern-forum.scss', 'css')
    .sass('resources/assets/sass/extern-fotoalbum.scss', 'css')
    .sass('resources/assets/sass/extern.scss', 'css')
    .sass('resources/assets/sass/general.scss', 'css')
    .sass('resources/assets/sass/effect/civisaldo.scss', 'css/effect')
    .sass('resources/assets/sass/effect/minion.scss', 'css/effect')
    .sass('resources/assets/sass/effect/onontdekt.scss', 'css/effect')
    .sass('resources/assets/sass/effect/snow.scss', 'css/effect')
    .sass('resources/assets/sass/effect/space.scss', 'css/effect')
    .sass('resources/assets/sass/module/agenda.scss', 'css/module')
    .sass('resources/assets/sass/module/bibliotheek.scss', 'css/module')
    .sass('resources/assets/sass/module/commissievoorkeuren.scss', 'css/module')
    .sass('resources/assets/sass/module/datatable.scss', 'css/module')
    .sass('resources/assets/sass/module/documenten.scss', 'css/module')
    .sass('resources/assets/sass/module/eetplan.scss', 'css/module')
    .sass('resources/assets/sass/module/fiscaat.scss', 'css/module')
    .sass('resources/assets/sass/module/formulier.scss', 'css/module')
    .sass('resources/assets/sass/module/forum.scss', 'css/module')
    .sass('resources/assets/sass/module/fotoalbum.scss', 'css/module')
    .sass('resources/assets/sass/module/ledenlijst.scss', 'css/module')
    .sass('resources/assets/sass/module/ledenmemory.scss', 'css/module')
    .sass('resources/assets/sass/module/maaltijdlijst.scss', 'css/module')
    .sass('resources/assets/sass/module/mededelingen.scss', 'css/module')
    .sass('resources/assets/sass/module/menubeheer.scss', 'css/module')
    .sass('resources/assets/sass/module/profiel.scss', 'css/module')
    .sass('resources/assets/sass/module/roodschopper.scss', 'css/module')
    .sass('resources/assets/sass/module/stamboom.scss', 'css/module')
    .sass('resources/assets/sass/opmaak/civitasia.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/dies.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/lustrum.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/normaal.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/owee.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/roze.scss', 'css/opmaak')
    .sass('resources/assets/sass/opmaak/sineregno.scss', 'css/opmaak');
