import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import OptimizeCSSAssetsPlugin from 'optimize-css-assets-webpack-plugin';
import path from 'path';
import TerserPlugin from 'terser-webpack-plugin';
import {VueLoaderPlugin} from 'vue-loader';
import webpack from 'webpack';
import ManifestPlugin from 'webpack-manifest-plugin';

const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

const contextPath = path.resolve(__dirname, 'resources/assets');

// De Webpack configuratie.
const config: (env: string, argv: any) => webpack.Configuration = (env, argv) => ({
	mode: 'development',
	context: contextPath,
	entry: {
		'app': './js/app.ts',
		'ledenmemory': './js/ledenmemory.ts',
		'fxclouds': './js/effect/fxclouds.ts',
		'fxonontdekt': './js/effect/fxonontdekt.ts',
		'fxtrein': './js/effect/fxtrein.ts',
		'fxraket': './js/effect/fxraket.ts',
		'fxminion': './js/effect/minion.ts',
		'fxclippy': './js/effect/fxclippy.ts',
		'extern': ['./js/extern.ts', './sass/extern.scss'],
		'bredeletters': './sass/bredeletters.scss',
		'common': './sass/common.scss',
		'extern-forum': './sass/extern-forum.scss',
		'extern-fotoalbum': './sass/extern-fotoalbum.scss',
		'maaltijdlijst': './sass/maaltijdlijst.scss',
		'thema-civitasia': './sass/thema/civitasia.scss',
		'thema-dies': './sass/thema/dies.scss',
		'thema-donker': './sass/thema/donker.scss',
		'thema-lustrum': './sass/thema/lustrum.scss',
		'thema-normaal': './sass/thema/normaal.scss',
		'thema-owee': './sass/thema/owee.scss',
		'thema-roze': './sass/thema/roze.scss',
		'thema-koevoet': './sass/thema/Koevoet.scss',
		'thema-sineregno': './sass/thema/sineregno.scss',
		'effect-civisaldo': './sass/effect/civisaldo.scss',
		'effect-snow': './sass/effect/snow.scss',
		'effect-space': './sass/effect/space.scss',
	},
	output: {
		// De map waarin alle bestanden geplaatst worden.
		path: path.resolve(__dirname, 'htdocs/dist'),
		// Alle javascript bestanden worden in de map js geplaatst.
		filename: argv.mode !== 'production' ? 'js/[name].js' : 'js/[name].[contenthash].js',
		chunkFilename: argv.mode !== 'production' ? 'js/[name].js' : 'js/[id].[contenthash].js',
		publicPath: '/dist/',
	},
	devtool: 'source-map',
	resolve: {
		// Vanuit javascript kun je automatisch .js en .ts bestanden includen.
		extensions: ['.ts', '.js', '.vue'],
		alias: {
			vue$: 'vue/dist/vue.esm.js',
		},
	},
	optimization: {
		minimizer: [
			new OptimizeCSSAssetsPlugin({}),
			new TerserPlugin(),
		],
	},
	plugins: [
		new MiniCssExtractPlugin({
			// Css bestanden komen in de map css terecht.
			filename: argv.mode !== 'production' ? 'css/[name].css' : 'css/[name].[contenthash].css',
		}),
		new VueLoaderPlugin(),
		new ManifestPlugin(),
		new MomentLocalesPlugin({
			localesToKeep: ['nl'],
		}),
	],
	module: {
		// Regels voor bestanden die webpack tegenkomt, als `test` matcht wordt de rule uitgevoerd.
		rules: [
			// Controleer .js bestanden met ESLint. Zie ook .eslintrc.js
			{
				enforce: 'pre',
				test: /\.(js|jsx)$/,
				exclude: [
					/node_modules/,
					/lib/,
				],
				use: 'eslint-loader',
			},
			{
				enforce: 'pre',
				test: /\.ts$/,
				exclude: [
					/node_modules/,
					/lib/,
					/\.vue\.tsx?/,
				],
				use: {
					loader: 'tslint-loader',
					options: {
						failOnHint: true,
					},
				},
			},
			{
				test: /\.vue.(ts|tsx)$/,
				exclude: /node_modules/,
				enforce: 'pre',
				use: [
					{
						loader: 'vue-tslint-loader',
						options: {
							failOnHint: true,
						},
					},
				],
			},
			// Verwerk .js bestanden met babel, dit zorgt ervoor dat alle nieuwe foefjes van javascript gebruikt kunnen worden
			// terwijl we nog wel oudere browsers ondersteunen.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [
					'cache-loader',
					{
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env'],
							plugins: [
								'@babel/syntax-dynamic-import',
								'@babel/plugin-proposal-class-properties',
								'@babel/plugin-transform-runtime',
								['@babel/plugin-proposal-decorators', {decoratorsBeforeExport: true}],
							],
						},
					},
				],
			},
			// Verwerk .ts (typescript) bestanden en maak er javascript van.
			{
				test: /\.ts$/,
				use: {
					loader: 'ts-loader',
					options: {
						// Controleert geen types.
						transpileOnly: true,
					},
				},
			},
			{
				test: /\.vue$/,
				use: {
					loader: 'vue-loader',
					options: {
						loaders: {
							ts: 'ts-loader!tslint-loader',
						},
					},
				},
			},
			// Verwerk sass bestanden.
			// `sass-loader` >
			// Compileer naar css
			// `resolve-url-loader` >
			// Zorg ervoor dat verwijzingen naar externe bestanden kloppen (sass was meerdere bestanden, css één)
			// `css-loader` >
			// Trek alle afbeeldingen/fonts waar naar verwezen wordt naar de dist/images map
			// `postcss-loader` >
			// Haal een autoprefixer over de css, deze zorgt ervoor dat eventuele vendor-prefixes (-moz-, -webkit-)
			// worden toegevoegd.
			// `MiniCssExtractPlugin` >
			// Normaal slaat webpack css op in javascript bestanden, zodat je ze makkelijk specifiek kan opvragen
			// hier zorgen we ervoor dat de css eruit wordt getrokken en in een los .css bestand wordt gestopt.
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader as string, // Om ts tevreden te houden.
						options: {
							// De css bestanden zitten in de css map, / is dus te vinden op ../
							publicPath: '../',
						},
					},
					'cache-loader',
					{
						loader: 'css-loader',
						options: {
							importLoaders: 3,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							ident: 'postcss',
							plugins: [require('autoprefixer')],
						},
					},
					{
						loader: 'resolve-url-loader',
						options: {
							//optie om error met yarn run dev te fixen als het klaagt over url() errors
							removeCR: true,
						},
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
				use: ['cache-loader', 'style-loader', 'css-loader'],
			},
			// Sla fonts op in de fonts map.
			{
				test: /\.(woff|woff2|eot|ttf|otf)$/,
				use: [{
					loader: 'file-loader',
					options: {
						name: 'fonts/[name].[ext]',
					},
				}],
			},
			// Sla plaetjes op in de images map.
			{
				test: /\.(png|svg|jpg|gif)$/,
				use: [{
					loader: 'file-loader',
					options: {
						name: 'images/[name].[ext]',
					},
				}],
			},
		],
	},
});

export default config;
