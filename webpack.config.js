const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const {VueLoaderPlugin} = require('vue-loader');
const path = require('path');

const devMode = process.env.NODE_ENV !== 'production';

let contextPath = path.resolve(__dirname, 'resources/assets');

// De Webpack configuratie.
module.exports = {
	mode: 'development',
	context: contextPath,
	entry: {
		'app': './js/app.js',
		'ledenmemory': './js/ledenmemory.js',
		'fxclouds': './js/effect/fxclouds.js',
		'fxonontdekt': './js/effect/fxonontdekt.js',
		'fxtrein': './js/effect/fxtrein.js',
		'fxminion': './js/effect/minion.ts',
		'extern': ['./js/extern.js', './sass/extern.scss'],
		'bredeletters': './sass/bredeletters.scss',
		'common': './sass/common.scss',
		'extern-forum': './sass/extern-forum.scss',
		'extern-fotoalbum': './sass/extern-fotoalbum.scss',
		'maaltijdlijst': './sass/maaltijdlijst.scss',
		'roodschopper': './sass/roodschopper.scss',
		'thema-civitasia': './sass/thema/civitasia.scss',
		'thema-dies': './sass/thema/dies.scss',
		'thema-lustrum': './sass/thema/lustrum.scss',
		'thema-normaal': './sass/thema/normaal.scss',
		'thema-owee': './sass/thema/owee.scss',
		'thema-roze': './sass/thema/roze.scss',
		'thema-sineregno': './sass/thema/sineregno.scss',
		'effect-civisaldo': './sass/effect/civisaldo.scss',
		'effect-snow': './sass/effect/snow.scss',
		'effect-space': './sass/effect/space.scss',
	},
	output: {
		// De map waarin alle bestanden geplaatst worden.
		path: path.resolve(__dirname, 'htdocs/dist'),
		// Alle javascript bestanden worden in de map js geplaatst.
		filename: devMode ? 'js/[name].js' : 'js/[name].[contenthash].js',
		chunkFilename: devMode ? 'js/[name].js' : 'js/[id].[contenthash].js',
		publicPath: '/dist/',
	},
	devtool: 'source-map',
	resolve: {
		// Vanuit javascript kun je automatisch .js en .ts bestanden includen.
		extensions: ['.ts', '.js', '.vue'],
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		}
	},
	optimization: {
		minimizer: [
			new OptimizeCSSAssetsPlugin({}),
			new UglifyJsPlugin(),
		]
	},
	plugins: [
		new MiniCssExtractPlugin({
			// Css bestanden komen in de map css terecht.
			filename: devMode ? 'css/[name].css' : 'css/[name].[contenthash].css'
		}),
		new VueLoaderPlugin(),
		new ManifestPlugin(),
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
							plugins: ['@babel/syntax-dynamic-import', '@babel/plugin-proposal-class-properties']
						},
					}
				],
			},
			// Verwerk .ts (typescript) bestanden en maak er javascript van.
			{
				test: /\.ts$/,
				exclude: /node_modules/,
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
				use: 'vue-loader'
			},
			// Verwerk sass bestanden.
			// `sass-loader` > Compileer naar css
			// `resolve-url-loader` > Zorg ervoor dat verwijzingen naar externe bestanden kloppen (sass was meerdere bestanden, css één)
			// `css-loader` > Trek alle afbeeldingen/fonts waar naar verwezen wordt naar de dist/images map
			// `postcss-loader` > Haal een autoprefixer over de css, deze zorgt ervoor dat eventuele vendor-prefixes (-moz-, -webkit-) worden toegevoegd.
			// `MiniCssExtractPlugin` > Normaal slaat webpack css op in javascript bestanden, zodat je ze makkelijk specifiek kan opvragen
			//		hier zorgen we ervoor dat de css eruit wordt getrokken en in een los .css bestand wordt gestopt.
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							// De css bestanden zitten in de css map, / is dus te vinden op ../
							publicPath: '../',
						},
					},
					'cache-loader',
					{
						loader: 'css-loader',
						options: {
							// url: false,
							sourceMap: devMode,
							minimize: !devMode,
							importLoaders: 3,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							sourceMap: devMode,
							ident: 'postcss',
							plugins: [require('autoprefixer')],
						},
					},
					{
						loader: 'resolve-url-loader',
						options: {}
					},
					{
						loader: 'sass-loader',
						options: {
							precision: 8,
							outputStyle: 'expanded',
							// Source maps moeten aan staan om `resolve-url-loader` te laten werken.
							sourceMap: true,
							sourceMapContents: false,
						},
					},
				],
			},
			{
				test: /\.css$/,
				use: ['cache-loader', 'style-loader', 'css-loader']
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
};
