const path = require('path');

const contextPath = path.resolve(__dirname, 'assets');

const terserPlugin = require('terser-webpack-plugin');
const OptimizeCssAssetsWebpackPlugin = require('optimize-css-assets-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const { VueLoaderPlugin } = require('vue-loader');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const ESLintPlugin = require('eslint-webpack-plugin');

module.exports = (env, argv) => {
	let styleEntries = {
		'thema-civitasia': './scss/thema/civitasia.scss',
		'thema-dies': './scss/thema/dies.scss',
		'thema-lustrum': './scss/thema/lustrum.scss',
		'thema-normaal': './scss/thema/normaal.scss',
		'thema-owee': './scss/thema/owee.scss',
		'thema-roze': './scss/thema/roze.scss',
		'thema-koevoet': './scss/thema/Koevoet.scss',
		'thema-sineregno': './scss/thema/sineregno.scss',
	};

	if (process.env.THEMA in styleEntries) {
		console.log('Maak alleen thema: ' + process.env.THEMA);

		styleEntries = { [process.env.THEMA]: styleEntries[process.env.THEMA] };
	}

	return {
		mode: 'development',
		context: contextPath,
		entry: {
			app: './js/entry/app.ts',
			sentry: './js/entry/sentry.ts',
			ledenmemory: './js/entry/ledenmemory.ts',
			fxclouds: './js/entry/fxclouds.ts',
			fxsneeuw: './js/entry/fxsneeuw.ts',
			fxonontdekt: './js/entry/fxonontdekt.ts',
			fxtrein: './js/entry/fxtrein.ts',
			fxraket: './js/entry/fxraket.ts',
			fxdruif: './js/entry/fxdruif.ts',
			fxminion: './js/entry/fxminion.ts',
			fxspace: './js/entry/fxspace.ts',
			extern: ['./js/entry/extern.ts', './scss/extern.scss'],
			bredeletters: './scss/bredeletters.scss',
			common: './scss/common.scss',
			'extern-forum': './scss/extern-forum.scss',
			'extern-fotoalbum': './scss/extern-fotoalbum.scss',
			'extern-owee': ['./js/entry/extern-owee.ts', './scss/extern-owee.scss'],
			maaltijdlijst: './scss/maaltijdlijst.scss',
			// Donker wordt altijd geladen
			'thema-donker': './scss/thema/donker.scss',
			...styleEntries,
			'effect-civisaldo': './scss/effect/civisaldo.scss',
			// lustrum-page related scss
			lustrum: './scss/lustrum12/style.scss',
			lustrumthema: './scss/lustrum12/thema.scss',
			lustrumweek: './scss/lustrum12/lustrumweek.scss',
			lustrumweek2: './scss/lustrum12/lustrumweek2.scss',
			lustrummerch: './scss/lustrum12/dikkemerch.scss',
			lustrumdiesthema: './scss/lustrum12/diesthema.scss',
			lustrumdies: './scss/lustrum12/dies.scss',
			'lustrum12-js': './js/lib/lustrum12.ts',
			lustrumreis: './scss/lustrum12/lustrumreis.scss',
			dies2023: './scss/dies2023/dies2023.scss',
		},
		output: {
			// De map waarin alle bestanden geplaatst worden.
			path: path.resolve(__dirname, 'htdocs/dist'),
			// Alle javascript bestanden worden in de map js geplaatst.
			filename:
				argv.mode !== 'production'
					? 'js/[name].bundle.js'
					: 'js/[name].[contenthash].bundle.js',
			chunkFilename:
				argv.mode !== 'production'
					? 'js/[name].chunk.js'
					: 'js/[name].[contenthash].chunk.js',
			publicPath: '/dist/',
			assetModuleFilename: 'images/[hash][ext][query]',
		},
		devtool: 'source-map',
		resolve: {
			// Vanuit javascript kun je automatisch .js en .ts bestanden includen.
			extensions: ['.ts', '.js'],
			fallback: {
				stream: false,
				util: false,
			},
		},
		cache: {
			type: 'filesystem',
		},
		optimization: {
			minimizer: [new OptimizeCssAssetsWebpackPlugin({}), new terserPlugin()],
			splitChunks: {
				chunks: 'all',
			},
		},
		plugins: [
			new (require('mini-css-extract-plugin'))({
				// Css bestanden komen in de map css terecht.
				filename:
					argv.mode !== 'production'
						? 'css/[name].css'
						: 'css/[name].[contenthash].css',
			}),
			new RemoveEmptyScriptsPlugin(),
			new VueLoaderPlugin(),
			new ESLintPlugin(),
			new WebpackAssetsManifest({
				entrypoints: true,
				integrity: true,
				entrypointsUseAssets: true,
			}),
			new MomentLocalesPlugin({
				localesToKeep: ['nl'],
			}),
		],
		module: {
			// Regels voor bestanden die webpack tegenkomt, als `test` matcht wordt de rule uitgevoerd.
			rules: [
				// Verwerk .ts (typescript) bestanden en maak er javascript van.
				{
					test: /\.ts$/,
					use: [
						{
							loader: 'ts-loader',
							options: {
								appendTsSuffixTo: [/\.vue$/],
								// We compilen jgallery ts
								allowTsInNodeModules: true,
							},
						},
					],
				},
				{
					test: /\.vue$/,
					loader: 'vue-loader',
				},
				// Verwerk sass bestanden.
				// `sass-loader` >
				// Compileer naar css
				// `resolve-url-loader` >
				// Zorg ervoor dat verwijzingen naar externe bestanden kloppen (sass was meerdere bestanden, css één)
				// `css-loader` >
				// Trek alle afbeeldingen/fonts waar naar verwezen wordt naar de dist/images map
				// `MiniCssExtractPlugin` >
				// Normaal slaat webpack css op in javascript bestanden, zodat je ze makkelijk specifiek kan opvragen
				// hier zorgen we ervoor dat de css eruit wordt getrokken en in een los .css bestand wordt gestopt.
				// css-cleanup-webpack-plugin is verantwoordelijk voor het verwijderen van leeggetrokken js bestanden.
				{
					test: /\.scss$/,
					use: [
						{
							loader: require('mini-css-extract-plugin').loader,
							options: {
								// De css bestanden zitten in de css map, / is dus te vinden op ../
								publicPath: '../',
							},
						},
						{
							loader: 'css-loader',
							options: {
								importLoaders: 3,
							},
						},
						{
							loader: 'postcss-loader',
							options: {
								postcssOptions: {
									plugins: [require('autoprefixer')],
								},
							},
						},
						{
							loader: 'resolve-url-loader',
							options: {},
						},
						{
							loader: 'sass-loader',
							options: {
								// Source maps moeten aan staan om `resolve-url-loader` te laten werken.
								sourceMap: true,
							},
						},
					],
				},
				{
					test: /\.css$/,
					use: ['style-loader', 'css-loader'],
				},
				// Sla fonts op in de fonts map.
				{
					test: /\.(woff|woff2|eot|ttf|otf)$/,
					type: 'asset',
					generator: {
						filename: 'fonts/[hash][ext][query]',
					},
				},
				// Sla plaetjes op in de images map.
				{
					test: /\.(png|svg|jpg|gif)$/,
					type: 'asset/resource',
					generator: {
						filename: 'images/[hash][ext][query]',
					},
				},
			],
		},
	};
};
